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
    private static $_log;
    private static $_registry = null;

    private function __construct()
    {
    }

    private static function _init()
    {
        if (!isset(self::$_registry)) {
            self::$_registry = AppServerRegistry::getInstance();
        }
        self::$_log = LogFactory::getInstance();
    }

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

    public static function startApp($appID)
    {
        self::_init();
        self::$_log = LogFactory::getInstance();
        self::$_log->debug(__METHOD__. ' called');
        if (self::$_registry->getAppStatus($appID) !== 'running') {
            throw new Exception("Instance of '$appID' not registered,".
                "cannot start it");
        }
        $instance = self::$_registry->getAppInstance($appID);
        $instance->summon();
    }
}