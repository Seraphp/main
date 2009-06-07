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
        self::$_log = LogFactory::getInstance($conf->server);
        self::$_log->debug(__METHOD__.' called');
        $this->_appID = (string)$conf['id'];
        self::$_log->debug('AppID: '.$this->_appID);
        $this->_pidFileName = sprintf('.%s_srphp.pid', $this->_appID);
        self::$_log->debug('PidFile name: '.$this->_pidFileName);
        self::$_log->debug('Including files');
        //Requireing all the files which are in the Config xml
        if (isset($conf->includes)) {
            foreach ($conf->includes as $key => $resource) {
                self::$_log->debug('Requireing '.$resource);
                if (require_once $resource) {
                    self::$_log->debug($resource. 'included');
                    array_push($this->_includes, $resource);
                }
            }
        }
        //Calling parent's constructor to initalize IPC if any
        if (isset($conf->instance)) {
            $instance = $conf->instance;
            //Calling Parent's constructor...
            if (isset($instance->ipc)) {
                self::$_log->debug('Initalizing IPC: '.$instance->ipc);
                parent::__construct($instance->ipc);
            } else {
                parent::__construct();
            }
            //Setting up server engine
            if (isset($instance->engine)) {
                self::$_log->debug('Initalizing engine: '.$instance->engine);
                //Class should be already "required-in" above
                $this->_engine = new $engine;
            } else {
                self::$_log->debug('Initalizing default engine');
                require_once 'Server/DefaultEngine.class.php';
                $this->_engine = new DefaultEngine;
            }
            //Setting up socket address and port
            if (isset($instance->address)) {
                $this->_address = $instance->address;
            } else {
                $this->_address = self::DEFAULT_ADDRESS;
            }
            if (isset($instance->port)) {
                $this->_port = $instance->port;
            } else {
                $this->_port = self::DEFAULT_PORT;
            }
            self::$_log->debug('Using '.
                sprintf('%s:%d',$this->_address, $this->_port));
        }
        self::$_log->debug('Setting up Application registry');
        if ($this->ipcType !== '') {
            require_once 'Server/Registry/IpcRegistry.class.php';
            $this->_appReg = IpcRegistry::getInstance();
            self::$_log->debug('Using IPCRegistry');
        } else {
            require_once 'Server/Registry/Registry.class.php';
            $this->_appReg = Registry::getInstance();
            self::$_log->debug('Using Registry');
        }
    }

    protected function onSummon()
    {
        self::$_log->debug(__METHOD__.' called');
        if ($this->_ipcType !== '') {
            $this->_appReg->useIpc($this->_ipc);
        }
        self::$_log->debug('Initalizing socket listening on');
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
        //Function usually called every 200 microsec
        if ($conn = @stream_socket_accept($this->_socket)) {
            self::$_log->debug(stream_get_meta_data($conn));
            self::$_log->debug('Connection accepted, spawning new child');
            $this->spawn();
            if ($this->_role == 'child') {//we are the new process
                $this->_accepting = false;
                //stream_socket_shutdown($this->_socket, STREAM_SHUT_RD);
                stream_set_blocking($conn, 0);
                try {
                    $result = $this->process(RequestFactory::create($conn));
                }catch (Exception $e) {
                    stream_socket_sendto($conn,
                        'HTTP/1.0 500 Internal Server Error');
                    self::$_log->alert('Error: '.$e->getMessage());
                    $result = 500;
                }
                stream_socket_shutdown($conn, STREAM_SHUT_RDWR);
                exit($result);
            }
            stream_socket_shutdown($conn, STREAM_SHUT_RDWR);
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
        self::$_log->debug(__METHOD__.' called');
        $this->_socket = stream_socket_server(sprintf('%s://%s:%s',
                'tcp',
                $this->_address,
                $this->_port),
            $errNum,
            $errMsg,
            STREAM_SERVER_BIND | STREAM_SERVER_LISTEN);
        if (!is_resource($this->_socket)) {
            throw new SocketException('Unable to open socket:'
                ."$errMsg ($errNum)");
        } else {
            self::$_log->debug('Setting listening socket to non blocking mode');
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
        self::$_log->debug(__METHOD__.' called');
        return $this->_engine->process($req);
    }

    /* (non-PHPdoc)
     * @see Server/Server#expell()
     */
    public function onExpell()
    {
        self::$_log->debug(__METHOD__.' called');
        self::$_log->log('closing down socket on '
                .$this->_address.':'
                .$this->_port);
        stream_socket_shtdown($this->_socket, STREAM_SHUT_RDWR);
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
        self::$_log->debug(__METHOD__.' called');
        self::$_log->debug('child exited: '.$pid.' with status:'.$status);
        if ($this->ipc !== null) {
            self::$_log->debug('Merging changes through IPC');
            $this->_appReg->mergeChanges();
        }
        unset($this->spawns[$pid]);
    }
}