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
        if ($socket !== null && is_resource($socket)) {
            $this->_socket = $socket;
            $this->toBeSend = true;
        } else {
            $this->toBeSend = false;
        }
    }

    public function send()
    {
        if ($this->toBeSend) {
            $this->statusLine = sprintf('HTTP/%s %d %s',
                                 $this->httpVersion,
                                 $this->statusCode,
                                 HttpFactory::getHttpStatus($this->statusCode));
            stream_set_write_buffer($this->_socket, 0);
            fwrite($this->_socket, $this->statusLine."\r\n");
            if ($this->headers !== array()) {
                fwrite($this->_socket,
                        implode("\r\n", $this->headers));
            }
            fwrite($this->_socket, "\r\n");
            if (!empty($this->messageBody)) {
                fwrite($this->_socket, $this->messageBody."\r\n");
            }
        }
    }
}