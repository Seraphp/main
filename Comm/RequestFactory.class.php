<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @copyright Copyright (c) 2008, Peter Nagy
 * @package Comm
 * @filesource
 */
/***/
//namespace Phaser\Comm;
/**
 * Class documentation
 *
 * @package Comm
 */
class RequestFactory{

    private function __construct(){}

    /**
     * Returns an object reference which is implements Request interface
     * @return Request
     */
    public static function create($socket)
    {
        //use socket_recv with MSF_PEEK to examin the first part
        // of the message without removing it form the socket.
    }

}
?>