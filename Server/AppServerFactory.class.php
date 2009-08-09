<?php
/**
 * File documentation
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @copyright Copyright (c) 2008, Peter Nagy
 * @version $Id$
 * @package Server
 * @filesource
 */
/***/
//namespace Seraphp\Server;
require_once 'Registry/AppServerRegistry.class.php';
/**
 * Class documentation
 *
 * @package Server
 */
class AppServerFactory
{
    /**
     * @var Log
     */
    private static $_log;
    /**
     * @var AppServerRegistry
     */
    private static $_registry = null;

    /**
     * Disabled private constructor
     *
     * @return void
     */
    private function __construct()
    {
    }

    /**
     * Initalize class variables
     *
     * @return void
     */
    private static function _init()
    {
        if (!isset(self::$_registry)) {
            self::$_registry = AppServerRegistry::getInstance();
        }
        self::$_log = LogFactory::getInstance();
    }

    /**
     * Gives back an JsonRpcProxy class
     *
     * @param Config $appID
     * @param $conf
     * @return AppServer|JsonRpcProxy
     */
    public static function getAppInstance($appID, Config $conf)
    {
        self::_init();
        self::$_log = LogFactory::getInstance($conf);
        self::$_log->debug(__METHOD__. ' called');
        if (self::$_registry->getAppStatus($appID) !== 'running') {
            try{
                $instance = new AppServer($conf);
            } catch(Exception $e) {
                self::$_log->alert($e->getMessage());
            }
            self::$_log->debug("New '$appID' instance created");
            self::$_registry->addApp($appID, $instance);
        } else {
            self::$_log->debug("Instance of '$appID' already exists");
            $instance = self::$_registry->getAppInstance($appID);
        }
        return $instance;
    }

    public static function storePid($name, $pid)
    {
        self::$_registry->storePid($name, $pid);
    }
}