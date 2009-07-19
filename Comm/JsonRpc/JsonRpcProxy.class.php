<?php
/**
 * Holds JsonRpcProxy implementation
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @copyright Copyright (c) 2009, Peter Nagy
 * @package Comm
 * @subpackage JsonRpc
 * @filesource
 */
/***/
//namespace Seraphp\Comm\JsonRpc;
require_once 'JsonRpcRequest.class.php';
require_once 'JsonRpcResponse.class.php';
/**
 * The class receives calls and translate them to RPC calls
 *
 * Received method calls which are not implemented in the class
 * will be cathced and sent to remote service as JSON-RPC method
 * calls. Result will be returned as method result orr Exceptions
 * will be thrown if error occurs.
 * Same class handles incoming RPC method calls by maintaining
 * the channel with any listener.
 *
 * @package Comm
 * @subpackage JsonRpc
 * @todo Test the class
 */
class JsonRpcProxy
{
    private static $_log;

    private $_name = '';

    private $_role = null;
    /**
     * @var mixed  Reference for client object
     */
    private $_client = null;
    /**
     * @var string  Connection type to use for RPC
     */
    private $_type = 'socket';
    /**
     * @var mixed  Reference for connection object
     */
    private $_conn = null;
    /**
     * @var array  Callable methods on our side
     */
    private $_allowedMethods = array();
    /**
     * @var array  Methodes at destianation which will have no return value
     */
    private $_notifications = array();

    private $_fifo = '';

    /**
     * @var integer Message ID counter
     */
    private static $_id = 0;

    private $_pid = null;

    /**
     * Sets up the class before opening any connection
     *
     * @param string $name  A string to name the connection
     * @param string $client  The client class whose methods are offered out
     *  for others
     * @param string $type  Connection type to use
     * @param array $methods  List of method calls to be proxied, if empty all
     * will be used
     * @param array $notifications  List of method calls which shouldn't have
     * return value from dest
     * @return JsonRpcProxy
     */
    public function __construct($name, $client = null,
            $type='socket', $methods = array(), $notifs = array())
    {
        self::$_log = LogFactory::getInstance($conf);
        self::$_log->debug(__METHOD__. ' called');
        $this->_type = $type;
        $this->_name = $name;
        if (isset($client)) {
            $this->setup($client, $methods, $notifs);
        }
    }

    /**
     *
     * @param stdObject|string $client  Any object whose methodes will be called
     *  through JsonRpcProxy
     * @param array $methods Specify methodes you want to make callable
     * @param array $notifications  Specify methodes(returning void) you want
     *  to make callable
     * @return void
     */
    public function setup($client, $methods = array(), $notifs = array())
    {
        self::$_log->debug(__METHOD__. ' called');
        switch (gettype($client)) {
            case 'array':
                $this->_client = $client[0];
                $this->_pid = $client[1];
                if (is_object($this->_client)) {
                    $clientClass = get_class($this->_client);
                } else {
                    $clientClass = $this->_client;
                }
                break;
            case 'object':
                $this->_client = $client;
                $clientClass = get_class($this->_client);
                break;
            default:
                $this->_client = $client;
                $clientClass = $this->_client;
        }
        $list = $this->_analyzeMethods($clientClass);
        if ($methods !== array()) {
            $this->_allowedMethods = array_intersect($methods,
                $list['methods']);
        } else {
            $this->_allowedMethods = $list['methods'];
        }
        if ($notifs !== array()) {
            $this->_notifications = array_intersect($notifs,
                $list['notifications']);
        } else {
            $this->_notifications = $list['notifications'];
        }
    }

    /**
     * Initalize the connection to start communication
     *
     * @param string $role  How to initalize the proxy: 'client'(default) or 'server'
     * @return boolean
     */
    public function init($role = 'client')
    {
        self::$_log->debug(__METHOD__. ' called');
        if($role == 'client' || $role == 'server') {
            $this->_role = $role;
        } else {
            throw new Exception('Role can only be client or server!');
        }
        switch ($this->_type) {
            case 'socket':
                @mkdir('/tmp/seraphp/', 0700);
                if ($this->_role == 'server') {
                    $this->_fifo['in'] = '/tmp/seraphp/'.$this->_name.'In.tmp';
                    $this->_fifo['out'] = '/tmp/seraphp/'.$this->_name.'Out.tmp';
                } else {
                    $this->_fifo['in'] = '/tmp/seraphp/'.$this->_name.'Out.tmp';
                    $this->_fifo['out'] = '/tmp/seraphp/'.$this->_name.'In.tmp';
                }
                foreach ($this->_fifo as $type => $fifo)
                if (!file_exists($fifo)) {
                    posix_mkfifo($fifo, 0700);
                }
                break;
        }
    }

    protected function _connect($mode)
    {
        if ($this->_role == 'client') {
            $this->_disconnect();
        }
        if ($mode == 'read') {
            $fifoDir = ($this->_role == 'client')?'out':'in';
            $this->_conn = fopen($this->_fifo[$fifoDir], 'r+');
            stream_set_blocking($this->_conn, false);
        } elseif ($mode == 'write') {
            $fifoDir = ($this->_role == 'client')?'in':'out';
            $this->_conn = fopen($this->_fifo[$fifoDir], 'w+');
            stream_set_blocking($this->_conn, false);
        }
    }

    protected function _disconnect()
    {
        if (is_resource($this->_conn)) {
            fclose($this->_conn);
        }
    }

    public function listen()
    {
        self::$_log->debug(__METHOD__.' called');
        $this->_connect('read');
        $read = array($this->_conn);
        self::$_log->debug($this->_conn);
        if (stream_select($read, $write = array(), $exc = array(), 5) > 0) {
            self::$_log->debug(__METHOD__. ': received something');
            $this->parseRequest(fgets($this->_conn));
        } else {
            self::$_log->debug(__METHOD__.' timed out');
        }
    }

    /**
     * Handle calls which has to be SENT to destination client
     *
     * @return mixed
     */
    public function __call($name, $arguments = array())
    {
        self::$_log->debug(__METHOD__. ' called');
        if (in_array($name, $this->_notifications) ) {
            $message = (string) new JsonRpcRequest($name, $arguments);
            self::$_log->debug('Message: '.$message);
            $this->_connect('write');
            if (fwrite($this->_conn, $message."\n") === false) {
                 throw new IOException('Cannot write FIFO: '.$this->_fifo);
            }
            $this->_sendSignal($this->_pid);
        } elseif (in_array($name, $this->_allowedMethods) ) {
            $message = (string) new JsonRpcRequest($name, $arguments, self::getID());
            $this->_connect('write');
            if (fwrite($this->_conn, $message."\n")) {
                $this->_sendSignal($this->_pid);
                $this->_connect('read');
                $read = array($this->_conn);
                if (stream_select($read, $write=array(), $exc= array(), 5) > 0) {
                    $reply = fgets($this->_conn);
                    if ($reply === false) {
                        throw new IOException('No reply in FIFO');
                    }
                    self::$_log->debug(__METHOD__. ' received:'.$reply);
                    return $this->_parseReply($reply);
                }
            } else {
                throw new IOException('Cannot write FIFO: '.$this->_fifo);
            }
        } else {
            throw new Exception(sprintf('No such function: %s::%s()'.
                $this->_client, $name));
        }
    }

    /**
     * Parse the JSON call's reply and throws Exception if
     * an error was sent back
     *
     * @param string $reply  JSON text to parse
     * @return mixed
     */
    private function _parseReply($reply)
    {
        self::$_log->debug(__METHOD__. ' called');
        $message = json_decode($reply);
        self::$_log->debug('Message: '.$reply);
        if (isset($message->error)) {
            $exception = $message->error;
            throw $exception;
        } else {
            return $message->result;
        }
    }

    /**
     * @param string $msg  JSON coded string
     * @return mixed
     */
    public function parseRequest($msg)
    {
        fwrite(STDOUT, __METHOD__.' received: '.$msg);
        self::$_log->debug(__METHOD__. ' called');
        self::$_log->debug('Message: '.$msg);
        $message = json_decode($msg);
        if (is_callable($this->_client, $message->method)) {
            self::$_log->debug('Method exists: '.$message->method);
            $error = null;
            try
            {
                $result = call_user_func_array(array($this->_client,
                    $message->method), $message->params);
            } catch(Exception $e) {
                $error = $e;
            }
            if ($message->id !== null ) {
                $response = (string) new JsonRpcResponse($result,
                    $error, $message->id);
                self::$_log->debug('Result is: '.$response);
                $this->_connect('write');
                fwrite($this->_conn['write'], $response."\n");
            }
        }
    }

    /**
     * Return a new message id
     *
     * @return integer
     */
    public static function getID()
    {
        return self::$_id++;
    }

    /**
     * Returns an array of 2 arrays about methodes and notifications
     *
     * @param string $className  Any class which has to be analized
     * @return array  Methodes which are publicly available, marking which have
     * no return value
     */
    protected function _analyzeMethods($className)
    {
        self::$_log->debug(__METHOD__. ' called');
        $pubMethods = array();
        $pubNotifs = array();
        $analyzer = new ReflectionClass($className);
        $methods = $analyzer->getMethods();
        for ($idx = 0; $idx < count($methods); $idx++) {
            if ($methods[$idx]->isPublic() &&
               !$methods[$idx]->isConstructor()) {
                if (strpos($methods[$idx]->getDocComment(), '@return void')) {
                    $pubNotifs[] = $methods[$idx]->getName();
                } else {
                    $pubMethods[] = $methods[$idx]->getName();
                }
            }
        }
        return array('methods' => $pubMethods, 'notifications'=>$pubNotifs);
    }

    protected function _sendSignal($pid)
    {
        if (is_numeric($pid)) {
            return posix_kill($pid, SIGUSR1);
        } else {
            throw new Exception('Invalid PID provided: '.$pid);
        }
    }

    /**
     * @return void
     */
    public function __destruct()
    {
        $this->_disconnect();
    }
}