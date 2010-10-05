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
namespace Seraphp\Server;
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
    public function __construct(Config\Config $conf)
    {
        self::$_log = \Seraphp\Log\LogFactory::getInstance($conf->server);
        $this->_appID = (string)$conf['id'];
        $this->_pidFileName = sprintf('.%s_srphp.pid', $this->_appID);
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
        //Adding all pathes listed in config "includes/path"
        //They will not be added to include path before daemon is
        //summoned to lock out other processes seeing those pathes
        foreach ($conf->includes as $key => $resource) {
            if (is_dir($resource)) {
                array_push($this->_includes, $resource);
            } else {
                throw new \Exception($resource.
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
    protected function _configInstance(Config\Config $instance)
    {
        //Calling Parent's constructor...
        if (isset($instance->ipc)) {
            parent::__construct((string) $instance->ipc);
        } else {
            parent::__construct();
        }

        if (isset($instance->engines)) {
            foreach ($instance->engines->children() as $engine) {
                $this->_engines[(string)$engine['id']] = $engine;
            }
        } else {
            $conf = '<engine id="default" class="Default" />';
            $this->_engines['default'] = new Config\Config($conf);
        }
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
        if ($this->_ipcType !== '') {
            require_once 'Server/Registry/IpcRegistry.class.php';
            $this->_appReg = \Seraphp\Comm\Ipc\IpcRegistry::getInstance();
        } else {
            require_once 'Server/Registry/Registry.class.php';
            $this->_appReg = \Seraphp\Server\Registry\Registry::getInstance();
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
        if ($this->_ipcType !== '') {
            $this->_appReg->useIpc($this->_ipc);
        }
        $this->_setupIncludePathes();
        $this->_initEngines();

        $this->_rpcProxy = new \Seraphp\Comm\Json\JsonRpcProxy($this->_appID);
        $this->_rpcProxy->setup(
            $this,
            array('getAppId', 'getStatus'),
            array('expel')
        );
        $this->_rpcProxy->init('server');
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
            $class = '\Seraphp\Server\\'.$conf['class'].'Engine';
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
        usleep(400);
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
        $read = array($this->_socket);
        if (socket_select($read, $w = array(), $e = array(), 0)) {
        //if ($conn = @socket_accept($this->_socket)) {
            self::$_log->info('Connection accepted, spawning new child ('.microtime().')');
            $this->spawn();
            if ($this->_role == 'child') {//we are the new process
                $conn = socket_accept($this->_socket);
                if (false === $conn) {
                    throw new \Seraphp\Exceptions\IOException(
                        socket_strerror(socket_last_error())
                    );
                }
                $this->_accepting = false;
                socket_set_nonblock($conn);
                try {
                    $result = $this->process(
                        \Seraphp\Server\Comm\RequestFactory::create($conn),
                        $this->_timeout
                    );
                } catch (\Seraphp\Exceptions\IOException $e) {
                    self::$_log->alert('Error: '.$e->getMessage());
                    //stream_socket_shutdown($conn, STREAM_SHUT_RDWR);
                    socket_shutdown($conn, 2);
                    $result = 0;
                } catch (\Exception $e) {
                    self::$_log->alert('Error: '.$e->getMessage());
                    //stream_socket_sendto(
                    fwrite(
                        $conn,
                        'HTTP/1.0 500 Internal Server Error'
                    );
                    $result = 1;
                }
                //stream_socket_shutdown($conn, STREAM_SHUT_RDWR);
                socket_shutdown($conn, 2);
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
        /*$this->_socket = stream_socket_server(
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
            throw new \Seraphp\Exceptions\SocketException(
                'Unable to open socket:'."$errMsg ($errNum)");
        } else {
            self::$_log->debug('Setting listening socket to non blocking mode');
            stream_set_blocking($this->_socket, false);
            $this->_accepting = true;
            return true;
        }*/
        $this->_socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (false === $this->_socket) {
            throw new \Seraphp\Exceptions\IOException(
                socket_strerror(socket_last_error())
            );
        }
        if (false === socket_bind(
            $this->_socket, $this->_address, $this->_port
        )) {
            throw new \Seraphp\Exceptions\IOException(
                socket_strerror(socket_last_error())
            );
        }
        /*if (false === socket_set_nonblock($this->_socket)) {
            throw new IOException(socket_strerror(socket_last_error()));
        }*/
        if (false === socket_listen($this->_socket)) {
            throw new \Seraphp\Exceptions\IOException(
                socket_strerror(socket_last_error())
            );
        }
        $this->_accepting = true;
        return true;
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
    public function process(\Seraphp\Server\Comm\Request $req)
    {
        $path = parse_url($req->url, PHP_URL_PATH);
        $engine = false;
        foreach ($this->_urimap as $uriEntry=>$uriParams) {
            if (stripos($path, $uriEntry) !== false) {
                break;
            }
        }
        if ($uriParams['engine'] === false) {
            $returnCode = 1;
            $response = $req->respond(
                'File not found!',
                array('statusCode'=>404)
            );
            $response->send();
        } else {
            if (array_key_exists($uriParams['engine'], $this->_engines)) {
                $returnCode =
                $this->_engines[$uriParams['engine']]->process($req);
            } else {
                $returnCode = 1;
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
        $this->_accepting = false;
        if (is_resource($this->_socket)) {
            //stream_socket_shutdown($this->_socket, STREAM_SHUT_RDWR);
            socket_shutdown($this->_socket, 2);
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
        if ($this->_ipc !== null) {
            $this->_appReg->mergeChanges();
        }
        unset($this->_spawns[$pid]);
    }

    protected function sigusr1Callback()
    {
        $this->_rpcProxy->listen();
    }
}