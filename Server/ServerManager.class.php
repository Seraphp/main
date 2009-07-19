<?php
/**
 * Contains implementation of ServerManager static class
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @copyright Copyright (c) 2008, Peter Nagy
 * @package Server
 * @filesource
 */
/***/
//namespace Seraphp\Server;
require_once 'Exceptions/ExceptionHandler.class.php';
require_once 'Server/AppServer.class.php';
require_once 'Server/Config/ConfigFactory.class.php';
require_once 'Server/AppServerFactory.class.php';
/**
 * ServerManager provides interface to start|stop|restart a server instance
 *
 * @package Server
 */
class ServerManager
{
    private static $_log;
    private static $_cf;
    private static $_reg;

    private function __construct()
    {
    }

    static function startup($appID='main')
    {
        self::_init();
        self::writeln('Starting up: '.$appID);
        $server = AppServerFactory::getAppInstance($appID,
                            self::$_cf->getConf($appID));
        $pid = $server->summon();
        var_dump(__METHOD__,$pid);
        self::$_reg->storePid($appID, $pid);
    }

    static function restart($appID)
    {
        self::_init();
        $currStatus = self::$_reg->getAppStatus($appID);
        if ($currStatus === 'running') {
            $oldProcess = self::$_reg->getAppInstance($appID);
            self::write('Starting up new server: '.$appID);
            $newProcess = AppServerFactory::getAppInstance($appID,
                                self::$_cf->getConf($appID));
            if ($newProcess === true) {
                self::writeln('...OK');
            } else {
                self::writeln('...Failed');
            }
            self::write('Shuting down old: '.$appID);
            $result = $oldProcess->expell();
            if ($result === true) {
                self::writeln('...OK');
                self::$_reg->removeApp($appID);
            } else {
                self::writeln('...Failed');
            }
            if ($newProcess === true) {
                self::$_reg->addApp($appID, $newProcess);
                self::$_reg->storePid($appID, $newProcess->summon());
            }
        } else {
            self::writeln($appID.' is '.$currStatus);
        }
    }

    static function shutdown($appID)
    {
        self::_init();
        $currStatus = self::$_reg->getAppStatus($appID);
        if ($currStatus == 'running') {
            $process = self::$_reg->getAppInstance($appID);
            self::write('Shuting down '.$appID);
            $result = $process->expell();
            if ($result === true) {
                self::writeln('...OK');
                self::$_reg->removeApp($appID);
            } else {
                self::writeln('...Failed');
            }
        } else {
            self::writeln($appID.' is '.$currStatus);
        }
    }

    static function write($message)
    {
        echo ($message);
        self::$_log->debug($message);
    }

    static function writeln($message)
    {
        self::write($message);
        echo "\n";
    }

    private static function _init()
    {
        ExceptionHandler::setup();
        self::$_log = LogFactory::getInstance();
        self::$_cf = ConfigFactory::getInstance();
        self::$_reg = AppServerRegistry::getInstance();
    }
}