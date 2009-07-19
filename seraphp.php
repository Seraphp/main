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
        default: echo '1st paramter has to be the required action:'.
         'start|stop|restart';
        break;
    }
} else {
    echo "Usage: ./seraphp.php ACTION SERVER [config]\n";
    echo "\n";
    echo "Available ACTIONS:\n";
    echo "start\t\t\tStarts a server\n";
    echo "stop\t\t\tStops a server\n";
    echo "restart\t\t\tRestart a server\n";
    echo "\n";
    echo "Available SERVERs:\n";
    echo "See your config file.\n";
    echo "\n";
    echo "Config(optional):\n";
    echo "An XML file containing the configuration for the servers you want to";
    echo " instatiate.\n";
    echo "Must be valid against seraphpConf.xsd!\n";
}