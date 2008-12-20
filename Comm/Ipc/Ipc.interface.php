<?php
/**
 * Holds IPC interface definition
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @copyright Copyright (c) 2008, Peter Nagy
 * @package Comm
 * @subpackage Ipc
 * @filesource
 */
/**
 * IPC interface definition
 * @package Comm
 * @subpackage Ipc
 */
interface Ipc{

    function init($pid);
    function read();
    function write($to, $message);
    function close();
}
?>