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
namespace Seraphp\Comm\JsonRpc;
require_once 'JsonRpcRequest.class.php';
require_once 'JsonRpcResponse.class.php';
require_once 'Exceptions/IOException.class.php';
/**
 * The class receives calls and translate them to RPC calls
 *
 * Received method calls which are not implemented in the class
 * will be cathced and sent to remote service as JSON-RPC method
 * calls. Result will be returned as method result or Exceptions
 * will be thrown if error occurs.
 * Same class handles incoming RPC method calls by maintaining
 * the channel with any listener.
 *
 * @package Comm
 * @subpackage JsonRpc
 */
class JsonRpcProxy
{
    const TIMEOUT = 10;

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
     * @var array  Reference for connection resources
     */
    private $_conn = array();
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
        self::$_log = \Seraphp\Log\LogFactory::getInstance();
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
                break;
        }
        $list = $this->_analyzeMethods($clientClass);
        if ($methods !== array()) {
            $this->_allowedMethods = array_intersect(
                $methods,
                $list['methods']
            );
        } else {
            $this->_allowedMethods = $list['methods'];
        }
        if ($notifs !== array()) {
            $this->_notifications = array_intersect(
                $notifs,
                $list['notifications']
            );
        } else {
            $this->_notifications = $list['notifications'];
        }
    }

    /**
     * Initalize the connection to start communication
     *
     * @param string $role  How to initalize the proxy: 'client'(default) or
     *  'server'
     * @return boolean
     * @throws IOException
     */
    public function init($role = 'client')
    {
        if ($role == 'client' || $role == 'server') {
            $this->_role = $role;
        } else {
            throw new \Exception('Role can only be client or server!');
        }
        switch ($this->_type) {
            case 'socket':
                if (!is_dir('/tmp/seraphp/')) {
                    mkdir('/tmp/seraphp/', 0700);
                }
                $this->_fifo['in'] = '/tmp/seraphp/'.$this->_name.'I.tmp';
                $this->_fifo['out'] = '/tmp/seraphp/'.$this->_name.'O.tmp';
                foreach ($this->_fifo as $type => $pipe) {
                    if (!file_exists($pipe)) {
                        if (posix_mkfifo($pipe, 0700) === false) {
                            throw new \Seraphp\Exceptions\IOException(
                                'Cannot create '.$pipe
                            );
                        }
                    }
                }
                break;
        }
        $this->_connect('read');
    }

    protected function _connect($mode)
    {
        switch ($mode) {
            case 'read':
                if ($this->_role == 'client') {
                    $fifo = $this->_fifo['out'];
                } else {
                    $fifo = $this->_fifo['in'];
                }
                break;
            case 'write':
                if ($this->_role == 'client') {
                    $fifo = $this->_fifo['in'];
                } else {
                    $fifo = $this->_fifo['out'];
                }
                break;
            default:
                return;
        }
        $this->_conn[$mode] = fopen($fifo, substr($mode, 0, 1).'+');
        stream_set_blocking($this->_conn[$mode], false);
    }

    /**
     * Depending on the $mode it closes the connection if exists.
     * Mode can be 'read' or 'write'.
     *
     * @param string $mode
     * @return boolean
     */
    protected function _disconnect($mode)
    {
        if ($mode == 'read' || $mode == 'write') {
            if (is_resource($this->_conn[$mode])) {
                return fclose($this->_conn[$mode]);
            } else {
                return false;
            }
        }
        return false;
    }

    /**
     * When called checks if there is a message to read and executes it.
     *
     * @return void
     */
    public function listen()
    {
        try {
            $this->parseRequest($this->_read());
        } catch(\Seraphp\Exceptions\IOException $e) {
            //TODO: Handle IOException correctly
        }
    }

    /**
     * Handle calls which has to be SENT to destination client
     *
     * @return mixed
     */
    public function __call($name, $arguments = array())
    {
        if (in_array($name, $this->_notifications) ) {
            $message = (string) new JsonRpcRequest($name, $arguments);
            try {
                $this->_write($message."\n");
            } catch (IOException $e) {
                self::$_log->warn($e->getMessage());
            }
        } elseif (in_array($name, $this->_allowedMethods) ) {
            $message = (string) new JsonRpcRequest(
                $name, $arguments, self::getID()
            );
            try {
                $this->_write($message."\n");
            } catch (\Seraphp\Exceptions\IOException $e) {
                self::$_log->warn($e->getMessage());
            }
            return $this->_parseReply($this->_read());
        } else {
            throw new \Exception(
                sprintf(
                    'No such function allowed: %s::%s()'. $this->_client, $name
                )
            );
        }
    }

    /**
     * Parse the JSON call's reply and throws Exception if
     * an error was sent back
     *
     * @param string $reply  JSON text to parse
     * @return mixed
     * @throws RuntimeException
     */
    private function _parseReply($reply)
    {
        $message = json_decode($reply);
        if (isset($message->error)) {
            throw new \Seraphp\Exceptions\RuntimeException($message->error);
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
        $message = json_decode($msg);
        if (null === $message->params) {
            $message->params = array();
        }
        if (is_callable(array($this->_client, $message->method))) {
            $error = null;
            try {
                $result = call_user_func_array(
                    array($this->_client, $message->method),
                    $message->params
                );
            } catch(Exception $e) {
                $error = $e;
            }
            if ($message->id !== null) {
                $response = (string) new JsonRpcResponse(
                    $result, $error, $message->id
                );
            }
        } else {
            $response = (string) new JsonRpcResponse(
                null,
                new RuntimeException(
                    'No such method:' .$message->method
                ),
                $message->id
            );
        }
        try {
            $this->_write($response);
        } catch (\Seraphp\Exceptions\IOException $e) {
            self::$_log->warn($e->getMessage());
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
        $pubMethods = array();
        $pubNotifs = array();
        $analyzer = new \ReflectionClass($className);
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

    /**
     * Sends SIGUSR1 signal to provided PID if process exists.
     *
     * @param integer $pid
     * @return boolean
     * @throws Exception
     */
    protected function _sendSignal($pid)
    {
        if (is_numeric($pid) && posix_kill($pid, 0)) {
            return posix_kill($pid, SIGUSR1);
        } else {
            throw new \Exception('Invalid PID provided: '.$pid);
        }
    }

    /**
     * Watches incoming fifo and if something arriving returns it.
     *
     * @return string
     * @throws IOException
     * @uses JsonRpcProxy::TIMEOUT
     */
    private function _read()
    {
        $read = array($this->_conn['read']);
        if (stream_select($read, $w = null, $x = null, self::TIMEOUT, 15) > 0) {
            unset($w, $e);
            if ($reply = fgets($this->_conn['read'])) {
                return $reply;
            } else {
                throw new \Seraphp\Exceptions\IOException('No reply in FIFO');
            }
        } else {
            throw new \Seraphp\Exceptions\IOException('FIFO read timed out!');
        }
    }

    /**
     * Writes message to outgoing fifo and closes it.
     *
     * @param string $msg
     * @return boolean
     * @throws IOException
     */
    private function _write($msg)
    {
        $this->_connect('write');
        if ($result = fwrite($this->_conn['write'], $msg)) {
            if ($this->_role != 'server') {
                try {
                    $this->_sendSignal($this->_pid);
                } catch (\Exception $e) {
                    self::$_log->warn($e->getMessage());
                }
            }
            $this->_disconnect('write');
        } else {
            $this->_disconnect('write');
            throw new \Seraphp\Exceptions\IOException(
                'Cannot write FIFO: '.$this->_fifo
            );
        }
        return $result;
    }

    /**
     * @return void
     */
    public function __destruct()
    {
        foreach ($this->_conn as $mode=>$conn) {
            if (is_resource($conn)) {
                $this->_disconnect($mode);
            }
        }
        if ($this->_role == 'server' && $this->_client->getRole() == 'parent') {
            foreach ($this->_fifo as $fifo) {
                unlink($fifo);
            }
        }
    }
}