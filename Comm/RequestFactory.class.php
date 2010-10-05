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
namespace Seraphp\Comm;
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
        self::$_log = \Seraphp\Log\LogFactory::getInstance();
        $read = array($socket);
        $write = null;
        $except = null;
        //if (stream_select($read, $write, $except, $timeout, 200) < 1) {
        if (socket_select($read, $write, $except, $timeout, 200) < 1) {
            throw new \Seraphp\Exceptions\IOException('Connection timed out!');
        }
        //using socket_recv with STREAM_PEEK to examin the first part
        //of the message without removing it form the socket.
        //$result = stream_socket_recvfrom($socket, 1500, STREAM_PEEK);
        socket_recv($socket, $result, 1500, STREAM_PEEK);
        if ($result !== null) {
            if (empty($result)) {
                throw new \Seraphp\Exceptions\IOException(
                    'Arrived data is empty!'
                );
            } else {
                switch (self::getProtocol($result)) {
                    case 'http':
                        require_once 'Comm/Http/HttpFactory.class.php';
                        return Http\HttpFactory::create('request', $socket);
                        break;
                }
            }
        } else {
            throw new \Seraphp\Exceptions\IOException('No data on line!');
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
        self::$_log = \Seraphp\Log\LogFactory::getInstance();
        if (preg_match('/^(GET|POST|HEAD) (.+) HTTP\/(\d\.\d)/', $data)) {
            return 'http';
        } else {
            return 'other';
        }
    }
}