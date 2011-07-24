<?php
/**
 * Contains AppServerRegistry class implementation and related Exceptions
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @copyright Copyright (c) 2008, Peter Nagy
 * @package Server
 * @subpackage Registry
 * @filesource
 */
/***/
namespace Seraphp\Server\Registry;
require_once 'Server/Registry/Registry.class.php';
require_once 'Server/AppServer.class.php';
require_once 'Exceptions/RegistryException.class.php';
require_once 'Comm/JsonRpc/JsonRpcProxy.class.php';
/**
 * Registry class of running AppServer instances
 *
 * @package Server
 * @subpackage Registry
 */
class AppServerRegistry extends Registry
{

    /**
     * Holds self reference
     *
     * @var Registry
     */
    static private $_instance = null;

    private function __construct(
        Seraphp\Server\Registry\StoreEngine $engine = null
    )
    {
        if (isset($engine)) {
            $this->setEngine($engine);
        }
    }
    /**
     * Returns instance of class
     *
     * @return self
     */
    public function getInstance(
        Seraphp\Server\Registry\StoreEngine $engine = null
    )
    {
        if (self::$_instance === null) {
            self::$_instance = new self($engine);
        }
        return self::$_instance;
    }

    /**
     * Gives back status of AppServer instance
     *
     * Return status string from AppServer instance if given key is
     * exists in the registry or Null
     *
     * @param string $appID
     * @return string
     */
    public function getAppStatus($appID)
    {
        $instance = $this->getAppInstance($appID);
        if ($instance == null || is_string($instance)) {
            return 'not running';
        } else {
            return $instance->getStatus();
        }
    }

    /**
     * Gives object reference of running AppServer
     *
     * Returns object reference of running AppServer instance if given
     * key is exists in the registry, or NUll.
     *
     * @param string $appID
     * @return String|JsonRpcProxy|null
     */
    public function getAppInstance($appID)
    {
        if (isset($this->$appID)) {
            if (!is_array($this->$appID) ||
                !self::_isPidExists($this->$appID[1])
            ) {
                //If a not running instance already exists
                return $this->$appID;
            }
            // If instance already running, return proxy to it.
            $proxy = new \Seraphp\Comm\JsonRpc\JsonRpcProxy(
                $appID, $this->$appID
            );
            $proxy->init();
            return $proxy;
        } else {
            return null;
        }
    }

    /**
     * Remove application from registry
     *
     * Returns reference of the removed application
     *
     * @param string $appID Id of the instance needs removal
     * @return JsonRpcProxy
     * @throws RegistryException  if id not exists in registry
     */
    public function removeApp($appID)
    {
        if (isset($this->$appID)) {
            $ref = $this->getAppInstance($appID);
            unset($this->$appID);
            return $ref;
        } else {
            throw new \Seraphp\Exceptions\RegistryException(
                'AppServer instance '.$appID.' not exists in registry!'
            );
        }
    }

    /**
     * Store reference of application in registry.
     *
     * @param string $appID
     * @param Server $appRef
     * @return boolean
     * @throws RegistryException
     */
    public function addApp($appID, \Seraphp\Server\Server $appRef)
    {
        if (!isset($this->$appID)) {
            $this->$appID = get_class($appRef);
            return true;
        } else {
            throw new \Seraphp\Exceptions\RegistryException(
                'AppServer already registered: '.$appID
            );
        }
    }

    /**
     * Stores the pid of a summoned server
     *
     * @param string $appID
     * @param integer $pid
     * @return boolean
     */
    public function storePid($appID, $pid)
    {
        if (isset($this->$appID)) {
            if (is_array($this->$appID)) {
                $this->$appID[1] = $pid;
            } else {
                $this->$appID = array($this->$appID, $pid);
            }
            return true;
        } else {
            throw new \Seraphp\Exceptions\RegistryException(
                'AppServer not yet registered: '.$appID
            );
        }
    }

    /**
     * Returns a summoned PID or false if no pid registered.
     *
     * @param string $appID
     * @return integer|boolean
     */
    public function getPid($appID)
    {
        if (isset($this->$appID) && is_array($this->$appID)) {
            $details = $this->$appID;
            return $details[1];
        } else {
            return false;
        }
    }
    /**
     * Tells if a process with that PID exists.
     *
     * @param integer $pid
     * @return boolean
     * @static
     */
    private static function _isPidExists($pid)
    {
        return posix_kill($pid, 0);
    }
}