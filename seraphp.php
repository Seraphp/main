#!/usr/bin/env php5
<?php
/**
 * Example startup file for Pahser server
 * @package Seraphp
 */
//set_error_handler('myErrorHandler', E_ERROR & E_WARNING);
require_once 'Server/AppServer.class.php';
require_once 'Server/Config/ConfigFactory.class.php';
$cf = ConfigFactory::getInstance();
$cf->setXmlSrc('/home/peter/workspace/seraphp/seraphpConf.xml');
$mainServer = new AppServer($cf->getConf('main'));
$procID = $mainServer->summon();

function myErrorHandler($errno, $errstr, $errfile, $errline)
{
    switch ($errno) {
    case E_ERROR:
        echo "ERROR [$errno] $errstr\n";
        echo "  Fatal error on line $errline in file $errfile";
        echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")\n";
        echo "Aborting...\n";
        exit(1);
        break;

    case E_WARNING:
        echo "WARNING [$errno] $errstr\n";
        break;

    case E_NOTICE:
        echo "NOTICE [$errno] $errstr\n";
        break;

    default:
        echo "Unknown error type: [$errno] $errstr\n";
        break;
    }

    /* Don't execute PHP internal error handler */
    return true;
}