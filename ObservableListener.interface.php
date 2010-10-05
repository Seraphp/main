<?php
/**
 * Hold Observableand Listener interface definitions
 *
 * @version $Id$
 * @filesource
 * @package Seraphp
 * @author Peter Nagy  <antronin@gmail.com>
 */
namespace Seraphp;
/**
 * Listener interface definition
 *
 * Classes implementing this interface will be able to receive
 * notification from classes implementing the Observable interface.
 *
 * @package Seraphp
 */
interface Listener
{
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
    public function update(\Seraphp\Observable $instance);
}
/**
 * Listener interface definition
 *
 * Classes implementing this interface will be able to
 * notify classes implementing the Observer interface.
 *
 * @package Seraphp
 */
interface Observable
{
    /**
     * Attaches an listener
     *
     * The instance will receives the update notifications.
     *
     * @param Listener $instance
     * @return boolean
     */
    public function attach(\Seraphp\Listener $instance);
    /**
     * Notifies listeners
     *
     * Flicks through all registered listeners and notify them about update
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
     * Deatches an listener
     *
     * The  instance will not receive updates anymore.
     *
     * @param string $str  Name of the Listener object
     * @return boolean
     */
    public function detach($str);
}