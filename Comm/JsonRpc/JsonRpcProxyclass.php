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
require_once "/Comm/JsonRpc/Request.class.php";
require_once "/Comm/JsonRpc/Response.class.php";
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
     * @var string  Connection's direction to allow
     */
    private $_dir = 'both';
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
     * @param string $src  The client whose methodes are offered out for others
     * @param string $dest  The client whose methodes can be called from here
     * @param string $type  Connection type to use
     * @param array $methods  List of method calls to be proxied, if empty all will be used
     * @param array $notifications  List of method calls which shouldn't have return value from dest
     * @return JsonRpcProxy
     */
    public function __construct($src, $dest, $type='socket', $srcMethods = array(), $destNotifications = array())
    {
        $this->_src = $src;
        $this->_dest = $dest;
        $this->_type = $type;
        $this->_dir = $direction;
        if ( $srcMethods !== array() ) {
            $this->_allowedMethods = $srcMethods;
        } else {
            $list = $this->analyzeClientMethods($this->_src);
            $this->_allowedMethods = $list['methods'];
        }
        if ( $destNotifications !== array() ) {
            $this->_notifications = $destNotifications;
        } else {
            $list = $this->analyzeClientMethods($this->_dest);
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
        switch( $this->_type ) {
            case 'socket':
                require_once 'Comm/Socket.class.php';
                $this->_connection = new Socket('unix', '/tmp/interSeraphp.tmp');
            break;
        }
    }

    /**
     * Handle calls which has to be SENT to destination client
     *
     * @return mixed
     */
    public function __call( $name, $arguments = array() )
    {
        if( in_array( $name, $this->_notifications ) ) {
            $message = (string) new Request( $name, $arguments );
            try {
                $this->_connection->writeLine( $_dest, $message );
            } catch(Exception $e) {
                throw $e;
            }
        } else {
            $message = (string) new Request( $name, $arguments, self::getID() );
            if ( $this->_connection->writeLine( $_dest, $message ) ) {
                return $this->parseReply( $this->_connection->readLine() );
            }
        }
    }

    /**
     * Parse the JSON call's reply and throws Exception if an error was sent back
     *
     * @param string $reply  JSON text to parse
     * @return mixed
     */
    private function parseReply($reply)
    {
        $message = json_decode($reply);
        if ( isset( $message->error ) ) {
            throw new Exception($message->error);
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
        if (is_callable($this->_src, $message->method)) {
        	$error = null;
            try {
        	    $result = call_user_func_array(
        	       array( $this->src,
        	              $message->method
        	       ),
        	       $message->params
        	    );
        	} catch( Exception $e )	{
        	    $error = $e;
        	}
        	if( $message->id !== null ) {
  	            $message = (string) new Response(
  	                                     $result,
  	                                     $error,
  	                                     $message->id
  	                                );
                $this->_connection->writeLine( $_dest, $message );
        	}
        }
    }

    /**
     * Return a new message id
     *
     * @return integer
     */
    static function getID()
    {
        return self::$_id++;
    }

    /**
     * Returns an array of 2 arrays about methodes and notifications
     *
     * @param mixed $obj  Any object which has to be analized
     * @return array  Methodes which are publicly available, marking which have no return value
     */
    public function analyzeClientMethods($obj)
    {
        $pubMethods = array();
        $pubNotifs = array();
        $analyzer = new ReflectionObject($obj);
        $methods = $analyzer->getMethods();
        for ( $idx = 0; $idx < count( $methods ); $idx++ ) {
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
        $this->_connection->disconnect();
    }
}