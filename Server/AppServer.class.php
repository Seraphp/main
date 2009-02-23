<?php
/**
 * Contains main AppServer class implementation
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @copyright Copyright (c) 2008, Peter Nagy
 * @version $Id$
 * @package Server
 * @filesource
 */
/***/
//namespace Seraphp\Server;
require_once 'Server/Server.class.php';
require_once 'Server/Config/Config.class.php';
require_once 'Comm/Request.interface.php';
require_once 'Comm/RequestFactory.class.php';
/**
 * AppServer implementation class
 *
 * @package Server
 */
class AppServer extends Server{

    protected $appID = '';
    protected $pidFolder = '/home/peter/workspace/seraphp';
    protected $appReg = null;
    protected $engine = null;
    private $includes = array();
    private $address = null;
    private $port = null;
    private $socket = null;
    private $accepting = false;

    const DEFAULT_ADDRESS = '127.0.0.1';
    const DEFAULT_PORT = 8080;

    /**
     * Constructor method fo initializing with a Config object
     *
     * @param Config $conf
     * @return AppServer
     */
    public function __construct(Config $conf)
    {
        $this->appID = $conf->name;
        //Requireing all the files which are in the Config xml
        if((isset($conf->includes)))
        {
            foreach($conf->includes as $key=>$resource)
            {
                if(require_once $resource)
                {
                    array_push($this->includes, $resource);
                }

            }
        }
        //Calling parent's constructor to initalize IPC if any
        if(isset($conf->instance))
        {
            $instance = $conf->instance;
            //Calling Parent's constructor...
            (isset($instance['ipc']))?parent::__construct($instance['ipc']):parent::__construct();
            //Setting up server engine
            if(isset($instance['engine']))
            {
                //Class should be already "required-in" above
                $this->engine = new $engine;
            }
            else
            {
                require_once 'Server/DefaultEngine.class.php';
                $this->engine = new DefaultEngine;
            }
            //Setting up socket address and port
            $this->address = (isset($instance['address']))?$instance['address']:self::DEFAULT_ADDRESS;
            $this->port = (isset($instance['port']))?$instance['port']:self::DEFAULT_PORT;
        }
        //Setting up Application registry
        if($this->ipcType !== '')
        {
            require_once 'Server/Registry/IpcRegistry.class.php';
            $this->appReg = IpcRegistry::getInstance();
        }
        else
        {
            require_once 'Server/Registry/Registry.class.php';
            $this->appReg = Registry::getInstance();
        }
    }

    protected function onSummon()
    {
        if($this->ipcType !== '')
        {
            $this->appReg->useIpc($this->ipc);
        }
        //Initalizing socket listening to
        $this->initSocket();
    }

    /**
     * Returns the appID string of the AppServer.
     *
     * @return string
     */
    public function getAppId()
    {
        return $this->appID;
    }


    /**
     * In everty cycle AppServer::listen() will be called,
     * which monitors the incoming connecton socket of the
     * server.
     *
     * @return void
     */
    protected function hartBeat()
    {
        if($this->accepting === true)
        {
            $this->listen();
        }
        usleep(200);
    }

    /**
     * Examine if there is any incoming conection waiting for accepting
     *
     * If there is, the connection load will be converted into a
     * Request Object wile a new child process will be forked, and in it
     * the process() method will be envoked with the Request object
     * as parameter. Th return value of the process() method will be the
     * exit value of the child process.
     *
     * @return void
     */
    private function listen()
    {
        if($conn = socket_accept($this->socket))
        {
            fputs(STDOUT, 'Connection accepted, spawning new child'."\n");
            $this->spawn();
            if($this->role == 'child')
            {//we are the new process
                $this->accepting = false;
                socket_close($this->socket);
                try {
                    $result = $this->process(RequestFactory::create($sock));
                }catch (Exception $e) {
                    socket_write($sock, '500 Internal Server Error');
                    socket_close($sock);
                    $result = 500;
                }
                exit($result);
            }
        }
        return;
    }

    /**
     * Method to create and setup listening socket for the server.
     *
     * The socket will be listened for incoming requests.
     *
     * @return boolean
     * @throws SocketException
     */
    private function initSocket()
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, getprotobyname('TCP'));
        if(!is_resource($this->socket))
        {
            throw new SocketException('Unable to open socket:'.socket_strerror(socket_last_error()));
        }
        if( socket_bind($this->socket, $this->address, $this->port) &&
            socket_listen($this->socket, $this->getMaxSpawns()*2) &&
            socket_set_nonblock($this->socket))
        {
            $this->accepting = true;
            return true;
        }
        else
        {
            throw new SocketException('Unable to open socket:'.socket_strerror(socket_last_error()));
        }
    }

    /**
     * Start the processing of the received Request.
     *
     * Method should return with an integer, which will be the exit
     * status of the child process doing the processing.
     *
     * @param Request $req
     * @return integer
     */
    public function process(Request $req)
    {
        return $this->engine->process($req);
    }

    /* (non-PHPdoc)
     * @see Server/Server#expell()
     */
    public function onExpell()
    {
        fputs(STDOUT, 'closing down socket on '.$this->address.':'.$this->port."\n");
        socket_close($this->socket);
    }

    /**
     * Handles child exited situation
     *
     * Reads and merges child data into appRegistry for newly
     * created children to use.
     *
     * @param integer $pid  Child process ID
     * @param integer $status  Not used
     * @return void
     */
    protected function sigchldCallback($pid, $status)
    {
        fputs(STDOUT, 'child exited: '.$pid.' with status:'.$status."\n");
        if($this->ipc !== null)
        {
            $this->appReg->mergeChanges();
        }
        unset($this->spawns[$pid]);
    }
}


?>