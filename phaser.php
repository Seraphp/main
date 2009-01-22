#!/usr/bin/env php5
<?php
/**
 * Example startup file for Pahser server
 * @package Phaser
 */
require_once 'Server/AppServer.class.php';
require_once 'Server/Config/ConfigFactory.class.php';
$cf = ConfigFactory::getInstance();
$cf->setXmlSrc('/home/peter/workspace/phaser/phaserConf.xml');
$mainServer = new AppServer($cf->getConf('main'));
$procID = $mainServer->summon();
?>