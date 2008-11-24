<?php
/**
 * Stores interface definition for a Singleton class
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 * @package Phaser
 */
//namespace Phaser;
/**
 * Realize a class with only one existing instance.
 * @package Phaser 
 */
interface Singleton{
    /**
     * Returns with an instance from the implementing class
     */
    public function getInstance();
}
?>