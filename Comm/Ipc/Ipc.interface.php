<?php
/**
 * File documentation
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @copyright Copyright (c) 2008, Peter Nagy
 * @filesource
 */
/**
 * Class documentation
 */
interface Ipc{

    function init($pid);
    function read();
    function write($to, $message);
    function close();
}
?>