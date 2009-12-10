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
require_once 'Comm/JsonRpc/JsonRpcProxy.class.php';
require_once 'Exceptions/SocketException.class.php';
/**
 * AppServer implementation class
 *
 * @package Server
 */
class AppServer extends Server
{
    /**
     * @var string
     */
    protected $_appID = '';
    /**
     * @var string
     */
    protected $_pidFolder = '.';
    /**
     * @var Registry
     */
    protected $_appReg = null;
    /**
     * @var array
     */
    protected $_engines = array();
    /**
     * @var array
     */
    private $_includes = array();
    /**
     * @var string
     */
    private $_address = null;
    /**
     * @var integer
     */
    private $_port = null;
    /**
     * @var resource
     */
    private $_socket = null;
    /**
     * @var boolean
     */
    private $_accepting = false;
    /**
     * @var array
     */
    private $_urimap = array();
    /**
     * @var RpcPRoxy
     */
    private $_rpcProxy = null;

    const DEFAULT_ADDRESS = '127.0.0.1';
    const DEFAULT_PORT = 8085;
    const DEFAULT_TIMEOUT = 90;

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
        if (isset($conf->includes)) {
            $this->_configIncludes($conf->includes);
        }
        //Calling parent's constructor to initalize IPC if any
        if (isset($conf->instance)) {
            $this->_configInstance($conf->instance);
        }
        $this->_configUrimap($conf);
        //This must be called lastly as it depends on other setting above.
        $this->_configRegistry();
    }

    /**
     * Configures Include paths for the AppServer instance
     *
     * @param Config $includes
     * @return void
     * @throws Exception
     */
    protected function _configIncludes($includes)
    {
        self::$_log->debug('Adding include pathes');
        //Adding all pathes listed in config "includes/path"
        //They will not be added to include path before daemon is
        //summoned to lock out other processes seeing those pathes
        foreach ($conf->includes as $key => $resource) {
            self::$_log->debug('path: '.$resource);
            if (is_dir($resource)) {
                array_push($this->_includes, $resource);
                self::$_log->debug($resource. 'added');
            } else {
                throw new Exception($resource.
                    ' is not a directory to include');
            }
        }
    }

    /**
     * Configures engine, IP, port and timeout for the AppServer instance
     *
     * @param Config $instance
     * @return void
     */
    protected function _configInstance($instance)
    {
        //Calling Parent's constructor...
        if (isset($instance->ipc)) {
            self::$_log->debug('Initalizing IPC: '.$instance->ipc);
            parent::__construct((string) $instance->ipc);
        } else {
            parent::__construct();
        }

        self::$_log->debug('Setting up server engines');
        if (isset($instance->engines)) {
            foreach ($instance->engines->children() as $engine) {
                $this->_engines[(string)$engine['id']] = $engine;
            }
        } else {
            self::$_log->debug('Initalizing default engines');
            $conf = '<engine id="default" class="Default" />';
            $this->_engines['default'] = new Config($conf);
        }
        self::$_log->debug('Setting up socket address, port and timeout');
        //setting up bind address
        if (isset($instance->address)) {
            $this->_address = (string) $instance->address;
        } else {
            $this->_address = self::DEFAULT_ADDRESS;
        }
        //Setting up port
        if (isset($instance->port)) {
            $this->_port = (string) $instance->port;
        } else {
            $this->_port = self::DEFAULT_PORT;
        }
        //Setting up timeout
        if (isset($instance->timeout)) {
            $this->_timeout = (integer) $instance->timeout;
        } else {
            $this->_timeout = self::DEFAULT_TIMEOUT;
        }
        self::$_log->info(
            'Using '.
            sprintf(
                '%s:%d w/ %d sec timeout',
                $this->_address,
                $this->_port,
                $this->_timeout
            )
        );
    }

    /**
     * Configures internal Registry for the AppServer instance
     *
     * If no ipcType is set, using standalone Registry class
     *
     * @return void
     */
    protected function _configRegistry()
    {
        self::$_log->debug('Setting up Application registry');
        if ($this->_ipcType !== '') {
            require_once 'Server/Registry/IpcRegistry.class.php';
            $this->_appReg = IpcRegistry::getInstance();
            self::$_log->debug('Using IpcRegistry');
        } else {
            require_once 'Server/Registry/Registry.class.php';
            $this->_appReg = Registry::getInstance();
            self::$_log->debug('Using Registry');
        }
    }

    /**
     * Configures URImaps for AppServer instance
     *
     * Adds Default entry as fallback engine if no other is specified
     *
     * @param Config $conf
     * @return void
     */
    protected function _configUrimap($conf)
    {
        self::$_log->debug('Setting up URImaps');
        if (isset($conf->urimap)) {
            foreach ($conf->urimap->children() as $node=>$value) {
                foreach ($value->attributes() as $attName=>$attValue) {
                    $this->_urimap[(string)$value][$attName] =
                    (string) $attValue;
                }
            }
        }
        //Adding default item as fallback
        $this->_urimap['/']['engine'] = 'default';
    }

    protected function onSummon()
    {
        self::$_log->debug(__METHOD__.' called');
        if ($this->_ipcType !== '') {
            $this->_appReg->useIpc($this->_ipc);
        }
        $this->_setupIncludePathes();
        $this->_initEngines();

        self::$_log->debug('Initalizing JsonRpc proxy');
        $this->_rpcProxy = new JsonRpcProxy($this->_appID);
        $this->_rpcProxy->setup(
            $this,
            array('getAppId', 'getStatus'),
            array('expell')
        );
        $this->_rpcProxy->init();
        self::$_log->debug('Initalizing socket listening on');
        $this->initSocket();
    }

    /**
     * Sets up process global include pathes
     *
     * Adds pathes from $this->_includes to include_path, if it is not already
     * in it.
     *
     * @return void
     */
    protected function _setupIncludePathes()
    {
        foreach ($this->_includes as $path) {
            $currIncludePath = get_include_path();
            if (strpos($currIncludePath, $path) === false) {
                set_include_path($currIncludePath . PATH_SEPARATOR . $path);
            }
        }
    }

    /**
     * Instantiates registered server engines
     *
     * Tryes to require every file stored in $this->_engines and if succeds
     * creates and instance of it and stores in place of the class name in
     * $this->_engines.
     *
     * @return void
     */
    protected function _initEngines()
    {
        foreach ($this->_engines as $name=>$conf) {
            $class = $conf['class'].'Engine';
            require_once $class.'.class.php';
            $this->_engines[$name] = new $class($conf);
        }
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
        usleep(100);
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
            self::$_log->debug('Connection accepted, spawning new child');
            $this->spawn();
            if ($this->_role == 'child') {//we are the new process
                $this->_accepting = false;
                stream_set_blocking($conn, 0);
                try {
                    $result = $this->process(
                        RequestFactory::create($conn),
                        $this->_timeout
                    );
                } catch (IOException $e) {
                    self::$_log->alert('Error: '.$e->getMessage());
                    stream_socket_shutdown($conn, STREAM_SHUT_RDWR);
                    $result = 0;
                } catch (Exception $e) {
                    self::$_log->alert('Error: '.$e->getMessage());
                    stream_socket_sendto(
                        $conn,
                        'HTTP/1.0 500 Internal Server Error'
                    );
                    $result = 1;
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
        $this->_socket = stream_socket_server(
            sprintf(
                '%s://%s:%s',
                'tcp',
                $this->_address,
                $this->_port
            ),
            $errNum,
            $errMsg,
            STREAM_SERVER_BIND | STREAM_SERVER_LISTEN
        );
        if (!is_resource($this->_socket)) {
            throw new SocketException(
                'Unable to open socket:'."$errMsg ($errNum)");
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
     * Selects the apropriate server engine to handle the request. If there is
     * no such (not registered or not loaded) it will generate 404 message.
     * Method should return with an integer, which will be the exit
     * status of the child process doing the processing.
     *
     * @param Request $req
     * @return integer
     */
    public function process(Request $req)
    {
        self::$_log->debug(__METHOD__.' called');
        $path = parse_url($req->url, PHP_URL_PATH);
        $engine = false;
        foreach ($this->_urimap as $uriEntry=>$uriParams) {
            if (stripos($path, $uriEntry) !== false) {
                break;
            }
        }
        self::$_log->debug("Engine: ".$uriParams['engine']);
        if ($uriParams['engine'] === false) {
            $returnCode = 1;
            self::$_log->debug("Not found: ".$uriParams['engine']);
            $response = $req->respond(
                'File not found!',
                array('statusCode'=>404)
            );
            $response->send();
        } else {
            if (array_key_exists($uriParams['engine'], $this->_engines)) {
                self::$_log->debug("Processing w/".$uriParams['engine']);
                $returnCode =
                $this->_engines[$uriParams['engine']]->process($req);
            } else {
                $returnCode = 1;
                self::$_log->debug("Not registered: ".$uriParams['engine']);
                $response = $req->respond(
                    'File not found!',
                    array('statusCode'=>500)
                );
                $response->send();
            }
        }
        return $returnCode;
    }

    /* (non-PHPdoc)
     * @see Server/Server#expell()
     */
    public function onExpel()
    {
        self::$_log->debug(__METHOD__.' called');
        if (is_resource($this->_socket)) {
            self::$_log->info(
                'closing down socket on '.
                $this->_address.
                ':'.
                $this->_port
            );
            stream_socket_shutdown($this->_socket, STREAM_SHUT_RDWR);
        }
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
        if ($this->_ipc !== null) {
            self::$_log->debug('Merging changes through IPC');
            $this->_appReg->mergeChanges();
        }
        unset($this->_spawns[$pid]);
    }

    protected function sigusr1Callback()
    {
        self::$_log->debug(__METHOD__.' called');
        $this->_rpcProxy->listen();
    }
}