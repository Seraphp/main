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
//namespace Seraphp\Server\Registry;
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

    private function __construct(StoreEngine $engine= null)
    {
        if (isset($engine)) {
            $this->setEngine($engine);
        } else {
            require_once 'Server/Registry/PackedFileDataStore.class.php';
            $this->setEngine(new PackedFileDataStore, './.srpdAppMan');
        }
    }
    /**
     * Returns instance of class
     *
     * @return self
     */
    public function getInstance(StoreEngine $engine = null)
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
     * @return string|Null
     */
    public function getAppStatus($appID)
    {
        if (isset($this->$appID)) {
            $instance = $this->getAppInstance($appID);
            return $instance->getStatus();
        } else return null;
    }

    /**
     * Gives object reference fo running AppServer
     *
     * Returns object reference of running AppServer instance if given
     * key is exists in the registry, or NUll.
     *
     * @param string $appID
     * @return JsonRpcProxy|null
     */
    public function getAppInstance($appID)
    {
        if (isset($this->$appID)) {
            $proxy = new JsonRpcProxy($appID, null, $this->$appID);
            $proxy->init();
            return $proxy;
        } else return null;
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
            throw new RegistryException(
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
    public function addApp($appID, Server $appRef)
    {
        if (!isset($this->$appID)) {
            $this->$appID = get_class($appRef);
            return true;
        } else {
            throw new RegistryException(
                'AppServer already registered: '.$appID
            );
        }
    }
}