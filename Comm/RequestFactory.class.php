<?php
/**
 * File documentation
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @copyright Copyright (c) 2008, Peter Nagy
 * @package Comm
 * @filesource
 */
/***/
//namespace Seraphp\Comm;
/**
 * Creates Request object based on socket load's first 100 byte
 *
 * @package Comm
 */
class RequestFactory
{
    private function __construct()
    {}

    private function __clone()
    {}

    /**
     * Returns an object reference which is implements Request interface
     *
     * @return Request
     */
    public static function create($socket)
    {
        //using socket_recv with MSF_PEEK to examin the first part
        //of the message without removing it form the socket.
        $result = socket_recv($socket, $peek, 100, MSG_PEEK);
        if( $result !== false ) {
            if ($peek !== null) {
                if ( $result == 0 ) {
                    throw new IOException('No data on line!');
                } else {
                    switch( self::getProtocol($peek) ) {
                        case 'http':
                            require_once 'Comm/Http/HttpFactory.class.php';
                            return HttpFactory::getMessage('request', $socket);
                        break;
                    }
                }
            }
        } else {
            if ( $result === false) {
                throw new IOException('Connection reset by peer!');
            } else {
                throw new IOException('Connection gracefully closed by peer!');
            }
        }
    }

    /**
     * Trys to identify the used protocol from the first bytes of the stream
     *
     * @param string $data
     * @return string
     * @todo implement more protocols to identify
     */
    public static function getProtocol($data)
    {
        if ( preg_match('/^(GET|POST|HEAD) (.+) HTTP\/(\d\.\d)/', $data) ) {
            return 'http';
        } else {
            return 'other';
        }
    }
}