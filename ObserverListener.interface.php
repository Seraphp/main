<?php
/**
 * Hold Observer and Listener interface definitions
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 * @package Phaser
 */
/**
 * Observer interface definition
 * 
 * Classes implementing this interface will be able to receive 
 * notification from classes implementing the Listener interface.
 * 
 * @package Phaser
 */
interface Observer{
    public function getName();
    public function update(Listener $instance);
    public function getState();
}
/**
 * Listener interface definition
 * 
 * Classes implementing this interface will be able to  
 * notify classes implementing the Observer interface.
 * 
 * @package Phaser
 */
interface Listener{
    public function attach(Observer $instance);
    public function notify();
    public function detach(Observer $instance);
}
?>