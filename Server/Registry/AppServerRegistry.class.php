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
//namespace Phaser\Server\Registry;
require_once 'Server/Registry/Registry.class.php';
require_once 'Server/AppServer.class.php';
/**
 * Registry class of running AppServer instances
 *
 * @package Server
 * @subpackage Registry
 */
class AppServerRegistry extends Registry{

    /**
     * Holds self reference
     *
     * @var Registry
     */
    static private $instance = null;

    private function __construct(){}
    /**
     * Returns instance of class
     *
     * @return self
     */
    public function getInstance()
    {
        if(self::$instance === null){
            self::$instance = new self;
        }
        return self::$instance;
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
        if(isset($this->$appID))
        {
            return $this->$appID->getStatus();
        }
        else return null;
    }

    /**
     * Gives object reference fo running AppServer
     *
     * Returns object reference of running AppServer instance if given
     * key is exists in the registry, or NUll.
     *
     * @param string $appID
     * @return AppServer|Null
     */
    public function getAppInstance($appID)
    {
        if(isset($this->$appID))
        {
            return $this->$appID;
        }
        else return null;
    }

    /**
     * Remove application from registry
     *
     * Returns reference of the removed application
     *
     * @param string $appID
     * @return AppServer
     * @throws RegistryException
     */
    public function removeApp($appID)
    {
        if(isset($this->$appID))
        {
            $ref = $this->$appID;
            unset($this->$appID);
            return $ref;
        }
        else throw new RegistryException('AppServer instance '.$appID.' not exists in registry!');
    }

    /**
     * Store reference of application in registry.
     *
     * @param string $appID
     * @param AppServer $appRef
     * @return boolean
     * @throws RegistryException
     */
    public function addApp($appID, AppServer $appRef)
    {
        if(!isset($this->$appID))
        {
            $this->$appID = $appRef;
            return true;
        }
        else throw new RegistryException('AppServer already registered: '.$appID);
    }
}

/**
 * RegistryException class
 *
 * @package Server
 * @subpackage Registry
 */
class RegistryException extends Exception{}
?>