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
require_once 'Exceptions/SocketException.class.php';
/**
 * AppServer implementation class
 *
 * @package Server
 */
class AppServer extends Server
{

    protected $_appID = '';
    protected $_pidFolder = '.';
    protected $_appReg = null;
    protected $_engine = null;
    private $_includes = array();
    private $_address = null;
    private $_port = null;
    private $_socket = null;
    private $_accepting = false;

    const DEFAULT_ADDRESS = '127.0.0.1';
    const DEFAULT_PORT = 8085;

    /**
     * Constructor method fo initializing with a Config object
     *
     * @param Config $conf
     * @return AppServer
     */
    public function __construct(Config $conf)
    {
        $this->_appID = $conf->name;
        $this->_pidFileName = sprintf('.%s_srphp.pid', $this->_appID);
        //Requireing all the files which are in the Config xml
        if ( isset($conf->includes) ) {
            foreach ($conf->includes as $key => $resource) {
                if (require_once $resource) {
                    array_push($this->_includes, $resource);
                }
            }
        }
        //Calling parent's constructor to initalize IPC if any
        if (isset($conf->instance)) {
            $instance = $conf->instance;
            //Calling Parent's constructor...
            if (isset($instance['ipc'])) {
                parent::__construct($instance['ipc']);
            } else {
                parent::__construct();
            }
            //Setting up server engine
            if (isset($instance['engine'])) {
                //Class should be already "required-in" above
                $this->_engine = new $engine;
            } else {
                require_once 'Server/DefaultEngine.class.php';
                $this->_engine = new DefaultEngine;
            }
            //Setting up socket address and port
            if (isset($instance['address'])) {
                $this->_address = $instance['address'];
            } else {
                $this->_address = self::DEFAULT_ADDRESS;
            }
            if (isset($instance['port'])) {
                $this->_port = $instance['port'];
            } else {
                $this->_port = self::DEFAULT_PORT;
            }
        }
        //Setting up Application registry
        if ($this->ipcType !== '') {
            require_once 'Server/Registry/IpcRegistry.class.php';
            $this->_appReg = IpcRegistry::getInstance();
        } else {
            require_once 'Server/Registry/Registry.class.php';
            $this->_appReg = Registry::getInstance();
        }
    }

    protected function onSummon()
    {
        if ($this->_ipcType !== '') {
            $this->_appReg->useIpc($this->_ipc);
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
        return $this->_appID;
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
        if ($this->_accepting === true) {
            $this->_listen();
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
    private function _listen()
    {
        if ($conn = @stream_socket_accept($this->_socket)) {
            fputs(STDOUT, 'Connection accepted, spawning new child'."\n");
            var_dump(stream_get_meta_data($conn));
            $this->spawn();
            if ($this->_role == 'child') {//we are the new process
                $this->_accepting = false;
                //@socket_close($this->_socket);
                @socket_set_nonblock($conn);
                try {
                    $result = $this->process(RequestFactory::create($conn));
                }catch (Exception $e) {
                    stream_socket_sendto($conn,
                        'HTTP/1.0 500 Internal Server Error');
                    fputs(STDOUT, 'Error: '.$e->getMessage()."\n");
                    $result = 500;
                }
                @socket_close($conn);
                exit($result);
            }
            @socket_close($conn);
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
        /*$this->_socket = socket_create(AF_INET, SOCK_STREAM, getprotobyname('TCP'));
        if (!is_resource($this->_socket)) {
            throw new SocketException('Unable to open socket:'
                .socket_strerror(socket_last_error()));
        }
        if ( socket_bind($this->_socket, $this->_address, $this->_port) &&
            socket_listen($this->_socket, $this->getMaxSpawns()*2) &&
            socket_set_nonblock($this->_socket)) {
                $this->_accepting = true;
                return true;
        } else {
            throw new SocketException('Unable to open socket:'
                .socket_strerror(socket_last_error()));
        }*/
        $this->_socket = stream_socket_server(sprintf('%s://%s:%s',
                'tcp',
                $this->_address,
                $this->_port),
            $errNum,
            $errMsg,
            STREAM_SERVER_BIND | STREAM_SERVER_LISTEN);
        if (!is_resource($this->_socket)) {
            throw new SocketException('Unable to open socket:'
                ."$errstr ($errno)");
        } else {
            stream_set_blocking($this->_socket, 0);
            $this->_accepting = true;
            return true;
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
        return $this->_engine->process($req);
    }

    /* (non-PHPdoc)
     * @see Server/Server#expell()
     */
    public function onExpell()
    {
        fputs(STDOUT, 'closing down socket on '
                .$this->_address.':'
                .$this->_port."\n");
        socket_close($this->_socket);
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
        if ($this->ipc !== null) {
            $this->_appReg->mergeChanges();
        }
        unset($this->spawns[$pid]);
    }
}