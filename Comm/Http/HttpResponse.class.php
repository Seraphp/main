<?php
/**
 * Contains HttpRequest class implementation.
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @copyright Copyright (c) 2009, Peter Nagy
 * @package Comm
 * @subpackage Http
 * @filesource
 */
/***/
//namespace Seraphp\Comm\Http;
//require_once 'ObserverListener.interface.php';
/**
 * Class represents an HTTP request, no matter
 * of the usage: sending or receiving it.
 *
 * @package Comm
 * @subpackage Http
 * @todo Implement HttpResponse class
 */
class HttpResponse
{
    private static $_log = null;
    public $statusLine = '';
    public $headers = array();
    public $cookies = array();
    public $contentType = 'text/plain';
    public $statusCode = 200;
    public $messageBody = '';
    public $httpVersion = '1.x';
    private $_socket = null;
    public $toBeSend = true;

    public function __construct($socket = null)
    {
        self::$_log = LogFactory::getInstance();
        self::$_log->debug(__METHOD__.' called');
        if ($socket !== null && is_resource($socket)) {
            $this->_socket = $socket;
            $this->toBeSend = true;
        } else {
            $this->toBeSend = false;
        }
    }

    public function send()
    {
        self::$_log->debug(__METHOD__.' called');
        if ($this->toBeSend) {
            $this->statusLine = sprintf('HTTP/%s %d %s',
                                 $this->httpVersion,
                                 $this->statusCode,
                                 HttpFactory::getHttpStatus($this->statusCode));
            self::$_log->debug('Setting write buffer to 0');
            stream_set_write_buffer($this->_socket, 0);
            self::$_log->debug('Writing status line');
            fwrite($this->_socket, $this->statusLine."\r\n");
            if ($this->headers !== array()) {
                self::$_log->debug('Writing headers');
                fwrite($this->_socket,
                        implode("\r\n", $this->headers));
            }
            fwrite($this->_socket, "\r\n");
            if (!empty($this->messageBody)) {
                self::$_log->debug('Writing body');
                fwrite($this->_socket, $this->messageBody."\r\n");
            }
            stream_socket_shutdown($this->_socket, STREAM_SHUT_RDWR);
        }
    }
}