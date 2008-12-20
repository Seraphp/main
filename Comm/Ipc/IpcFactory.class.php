<?php
/**
 * File documentation
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @copyright Copyright (c) 2008, Peter Nagy
 * @filesource
 */
/***/
require_once 'Comm/Ipc/Ipc.interface.php';
/**
 * Class documentation
 */
class IpcFactory{

    /**
     * Returns a object which implements Ipc interface
     *
     * @param string $type
     * @param integer $pid
     * @return Ipc
     * @static
     */
    static function get($type,$pid){
        return;
    }
}
?>