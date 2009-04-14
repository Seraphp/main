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
    private function __construct()
    {
    }

    static function startup($appID='main')
    {
        self::write('Starting up: '.$appID);
        $result = AppServerFactory::getAppInstance($appID,
                                ConfigFactory::getConf($appID));
        if ($result === true) {
            self::writeln('...OK');
        } else {
            self::writeln('...Failed');
        }
    }

    static function restart($appID)
    {
        $currStatus = AppServerRegistry::getAppStatus($appID);
        if ($currStatus === 'running') {
            $oldProcess = AppServerRegistry::getAppInstance($appID);
            self::write('Starting up new server: '.$appID);
            $newProcess = AppServerFactory::getAppInstance($appID,
                                ConfigFactory::getConf($appID));
            if ($newProcess === true) {
                self::writeln('...OK');
            } else {
                self::writeln('...Failed');
            }
            self::write('Shuting down old: '.$appID);
            $result = $oldProcess->expell();
            if ($result === true) {
                self::writeln('...OK');
                AppServerRegistry::removeApp($appID);
            } else {
                self::writeln('...Failed');
            }
            if ($newProcess === true) {
                AppServerRegistry::addApp($appID, $newProcess);
            }
        } else {
            self::writeln($appID.' is '.$currStatus);
        }
    }

    static function shutdown($appID)
    {
        $currStatus = AppServerRegistry::getAppStatus($appID);
        if ($currStatus == 'running') {
            $process = AppServerRegistry::getAppInstance($appID);
            self::write('Shuting down '.$appID);
            $result = $process->expell();
            if ($result === true) {
                self::writeln('...OK');
                AppServerRegistry::removeApp($appID);
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
    }

    static function writeln($message)
    {
        self::write($message);
        echo "\n";
    }

}