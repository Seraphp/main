<?php
/**
 * Holds IpcAdapter interface definition
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id:IpcAdapter.interface.php 321 2009-01-06 18:44:47Z peter $
 * @copyright Copyright (c) 2008, Peter Nagy
 * @package Comm
 * @subpackage Ipc
 * @filesource
 */
/***/
//namespace Seraphp\Comm\Ipc;
/**
 * IpcAdapter interface definition
 *
 * @package Comm
 * @subpackage Ipc
 */
interface IpcAdapter
{

    /**
     * Initalize adapter
     *
     * @param $pid  Process ID
     * @param $role  Child or Parent
     * @return void
     */
    function init($pid, $role);
    /**
     * Reads form the Adapter and return result string or false on error
     *
     * @return string|false
     */
    function read();
    /**
     * Sends message to recipient through adapter
     *
     * @param string $to  Recipient ID of the message
     * @param string $message  Meddage to deliver
     * @return boolean  True onSuccess, false on failure
     */
    function write($to, $message);
    /**
     * Sets role in the communication
     *
     * @param string $role  'child' or 'parent' arte accepted only
     * @return string  'child' or 'parent' accordingly
     */
    function setRole($role);
    /**
     * Returns current role
     * @return string 'child' or 'parent' accordingly
     */
    function getRole();
    /**
     * Closes any open connection
     * @return void
     */
    function close();
}