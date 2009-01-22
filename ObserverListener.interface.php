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
    /**
     * Returns a unique name of the Observer instance
     *
     * @return string
     */
    public function getName();
    /**
     * Notification method
     *
     * Calling this method will mean, that the Listener's status has been
     * changed.
     *
     * @param Listener $instance
     * @return void
     */
    public function update(Listener $instance);
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
    /**
     * Attaches an observer
     *
     * The instance will receives the update notifications.
     *
     * @param Observer $instance
     * @return boolean
     */
    public function attach(Observer $instance);
    /**
     * Notifies observers
     *
     * Flicks through all registered observers and notify them about update
     * @return void
     */
    public function notify();
     /**
     * Return observed status report
     *
     * @return mixed
     */
    public function getState();
    /**
     * Deatches an observer
     *
     * The  instance will not receive updates anymore.
     *
     * @param Observer $instance
     * @return boolean
     */
    public function detach(Observer $instance);
}
?>