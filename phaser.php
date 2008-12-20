#!/usr/bin/env php5
<?php
/**
 * Example startup file for Pahser server
 * @package Phaser
 */
require_once 'Server/AppServer.class.php';
$mainServer = new AppServer('main');
$mainServer->summon();
?>