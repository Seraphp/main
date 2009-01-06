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
/**
 * IpcAdapter interface definition
 * @package Comm
 * @subpackage Ipc
 */
interface IpcAdapter{

    function init($pid, $role);
    function read();
    function write($to, $message);
    function setRole($role);
    function getRole();
    function close();
}
?>