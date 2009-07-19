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
require_once 'Exceptions/IOException.class.php';
/**
 * Creates Request object based on socket load's first 100 byte
 *
 * @package Comm
 */
class RequestFactory
{
    private static $_log;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * Returns an object reference which is implements Request interface
     *
     * @param resource $socket  Stream socketalready open
     * @param integer $timeout  Timeout in secs to close if no data is arrived
     * @return Request
     * @throws IOException  If no data in socket arrived
     */
    public static function create($socket, $timeout=30)
    {
        self::$_log = LogFactory::getInstance();
        self::$_log->debug(__METHOD__.' called');
        $read = array($socket);
        $write = null;
        $except = null;
        if (stream_select($read, $write, $except, $timeout, 200) < 1) {
            throw new IOException('Connection timed out!');
        }
        self::$_log->debug('Data arriving on socket');
        //using socket_recv with MSF_PEEK to examin the first part
        //of the message without removing it form the socket.
        $result = stream_socket_recvfrom($socket, 300, STREAM_PEEK);
        if ($result !== null) {
            if (empty($result)) {
                throw new IOException('No data on line!');
            } else {
                switch( self::getProtocol($result) ) {
                    case 'http':
                        require_once 'Comm/Http/HttpFactory.class.php';
                        return HttpFactory::create('request', $socket);
                        break;
                }
            }
        } else {
            throw new IOException('No data on line!');
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
        self::$_log = LogFactory::getInstance();
        self::$_log->debug(__METHOD__.' called');
        if ( preg_match('/^(GET|POST|HEAD) (.+) HTTP\/(\d\.\d)/', $data) ) {
            self::$_log->debug('Data is HTTP');
            return 'http';
        } else {
            self::$_log->debug('Data is something unknow');
            return 'other';
        }
    }
}