#!/usr/bin/env php5
<?php
/**
 * Example startup file for Seraphp server
 * @package Seraphp
 */
require_once 'Exceptions/ExceptionHandler.class.php';
ExceptionHandler::setup();
require_once 'Server/AppServer.class.php';
require_once 'Server/Config/ConfigFactory.class.php';
$cf = ConfigFactory::getInstance();
$cf->setXmlSrc('./seraphpConf.xml');
$mainServer = new AppServer($cf->getConf('main'));
$procID = $mainServer->summon();