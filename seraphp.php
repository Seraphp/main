#!/usr/bin/env php5
<?php
/**
 * Example startup file for Seraphp server
 * @package Seraphp
 */
require_once 'Server/ServerManager.class.php';
$cf = ConfigFactory::getInstance();
if ($argc > 1) {
    if (isset($argv[2])) {
        $server = $argv[2];
    } else {
        $server = 'main';
    }
    if (isset($argv[3])) {
        $cf->setXmlSrc($argv[3]);
    } else {
        $cf->setXmlSrc('./seraphpConf.xml');
    }
    switch ($argv[1]) {
        case 'start':
            ServerManager::startup($server);
            break;
        case 'stop':
            ServerManager::shutdown($server);
            break;
        case 'restart':
            ServerManager::restart($server);
            break;
        default: echo '1st paramter has to be the required action: start|stop|restart';
    }
} else {
}