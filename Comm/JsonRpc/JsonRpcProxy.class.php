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
    /**
     * @var mixed  Reference of proxied object
     */
    private $_src = null;
    /**
     * @var mixed  Reference for callable JSON-RPC object
     */
    private $_dest = null;
    /**
     * @var string  Connection type to use for RPC
     */
    private $_type = 'socket';
    /**
     * @var mixed  Reference for connection object
     */
    private $_connection = null;
    /**
     * @var array  Callable methods on our side
     */
    private $_allowedMethods = array();
    /**
     * @var array  Methodes at destianation which will have no return value
     */
    private $_notifications = array();

    /**
     * @var integer Message ID counter
     */
    private static $_id = 0;

    /**
     * Sets up the class before opening any connection
     *
     * @param string $name  A string to name the connection
     * @param string $src  The client class whose methods are offered out for others
     * @param string $dest  The destination class whose methods can be called
     * @param string $type  Connection type to use
     * @param array $methods  List of method calls to be proxied, if empty all
     * will be used
     * @param array $notifications  List of method calls which shouldn't have
     * return value from dest
     * @return JsonRpcProxy
     */
    public function __construct($name, $src = null, $dest = null,
            $type='socket', $srcMethods = array(), $destNotifications = array())
    {
        self::$_log = LogFactory::getInstance($conf);
        self::$_log->debug(__METHOD__. ' called');
        $this->_type = $type;
        $this->_name = $name;
        if (isset($src)) {
            $this->addSrcObject($src, $srcMethods);
        }
        if (isset($dest)) {
            $this->addDestObject($dest, $destNotifications);
        }
    }

    public function addSrcObject($src, $methods = array(),
        $notifications = array())
    {
        self::$_log->debug(__METHOD__. ' called');
        $this->_src = $src;
        $list = $this->analyzeClientMethods($this->_src);
        if ($methods !== array()) {
            $this->_allowedMethods = array_intersect($methods,
                $list['methods']);
        } else {
            $this->_allowedMethods = $list['methods'];
        }
        if ($notifications !== array()) {
            $this->_notifications = array_intersect($notifications,
                $list['notifications']);
        } else {
            $this->_notifications = $list['notifications'];
        }
    }

    public function addDestObject($dest, $methods = array(),
        $notifications = array())
    {
        self::$_log->debug(__METHOD__. ' called');
        $this->_dest = $dest;
        $list = $this->analyzeClientMethods($this->_dest);
        if ($methods !== array()) {
            $this->_allowedMethods = array_intersect($methods,
                $list['methods']);
        } else {
            $this->_allowedMethods = $list['methods'];
        }
        if ($notifications !== array()) {
            $this->_notifications = array_intersect($notifications,
                $list['notifications']);
        } else {
            $this->_notifications = $list['notifications'];
        }
    }

    /**
     * Initalize the connection to start communication
     *
     * @return boolean
     */
    public function init()
    {
        self::$_log->debug(__METHOD__. ' called');
        switch ($this->_type) {
            case 'socket':
                require_once 'Comm/Socket.class.php';
                @mkdir('/tmp/seraphp/', 0700);
                $this->_connection = new Socket('unix',
                    '/tmp/seraphp/'.$this->_name.'.tmp');
                if (!$this->_connection->isConnected()) {
                    $this->_connection->connect();
                }
                $this->_connection->setBlocking(false);
                break;
        }
    }

    public function listen()
    {
        if ($this->_connection->select(Socket::READ, 0, 20) == Socket::READ) {
            self::$_log->debug(__METHOD__. ': received something');
            $this->parseRequest($this->_connection->readLine());
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
            try {
                $this->_connection->writeLine($message);
            } catch(Exception $e) {
                throw $e;
            }
        } else {
            $message = (string) new JsonRpcRequest($name, $arguments, self::getID());
            if ($this->_connection->writeLine($message)) {
                usleep(300);//letting readers read the message
                if ($this->_connection->select(Socket::READ, 0, 20) == Socket::READ) {
                    $reply = $this->_connection->readLine();
                    self::$_log->debug(__METHOD__. ' received:'.$reply);
                    return $this->_parseReply($reply);
                }
            } else {
                throw new IOException('Cannot write to socket');
            }
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
        if ( isset($message->error) ) {
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
        self::$_log->debug(__METHOD__. ' called');
        self::$_log->debug('Message: '.$msg);
        $message = json_decode($msg);
        if (is_callable($this->_src, $message->method)) {
            self::$_log->debug('Method exists: '.$message->method);
            $error = null;
            try {
                $result = call_user_func_array(array($this->_src,
                                                     $message->method),
                                               $message->params);
            } catch(Exception $e)	{
                $error = $e;
            }
            if ( $message->id !== null ) {
                $response = (string) new JsonRpcResponse($result,
                                                 $error,
                                                 $message->id);
                self::$_log->debug('Result is: '.$response);
                $this->_connection->writeLine($response);
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
     * @param string $class  Any class which has to be analized
     * @return array  Methodes which are publicly available, marking which have
     * no return value
     */
    public function analyzeClientMethods($class)
    {
        self::$_log->debug(__METHOD__. ' called');
        $pubMethods = array();
        $pubNotifs = array();
        $analyzer = new ReflectionClass($class);
        $methods = $analyzer->getMethods();
        for ( $idx = 0; $idx < count($methods); $idx++ ) {
            if ( $methods[$idx]->isPublic() &&
               !$methods[$idx]->isConstructor()
            ) {
                if ( strpos($methods[$idx]->getDocComment(), '@return void') ) {
                    $pubNotifs[] = $methods[$idx]->getName();
                } else {
                    $pubMethods[] = $methods[$idx]->getName();
                }
            }
        }
        return array('methods' => $pubMethods, 'notifications'=>$pubNotifs);
    }

    /**
     * @return void
     */
    public function __destruct()
    {
        if ($this->_connection->isConnected()) {
            $this->_connection->disconnect();
        }
    }
}