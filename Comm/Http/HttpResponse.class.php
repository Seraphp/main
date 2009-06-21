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
            $this->headers['Server'] = 'Seraphp 0.1';
        } else {
            $this->toBeSend = false;
        }
    }

    /**
     *
     *
     * @return void
     */
    public function send()
    {
        self::$_log->debug(__METHOD__.' called');
        if ($this->toBeSend) {
            $this->headers['Date'] = date(DATE_RFC1123);
            $this->statusLine = sprintf('HTTP/%s %d %s',
                                 $this->httpVersion,
                                 $this->statusCode,
                                 HttpFactory::getHttpStatus($this->statusCode));
            self::$_log->debug('Setting write buffer to 0');
            stream_set_write_buffer($this->_socket, 0);
            self::$_log->debug('Sending status line');
            self::$_log->debug($this->statusLine);
            if (fwrite($this->_socket, $this->statusLine."\r\n") === false) {
                throw new IOException('Cannot send status line!');
            }
            if (is_resource($this->messageBody)) {
                $data = fstat($this->messageBody);
                $this->headers['Content-Length'] = $data['size'];
                $this->headers['Last-Modified'] =
                    date(DATE_RFC1123, $data['mtime']);
            }else{
                $this->headers['Content-Length'] = strlen($this->messageBody);
            }
            if ($this->headers !== array()) {
                self::$_log->debug('Sending headers');
                if (fwrite($this->_socket, $this->_headers()."\r\n")) {
                    throw new IOException('Cannot send headers!');
                }
            }
            fwrite($this->_socket, "\r\n");
            if (!empty($this->messageBody)) {
                self::$_log->debug('Sending message body');
                if (is_resource($this->messageBody)) {
                    if (stream_copy_to_stream($this->messageBody,
                        $this->_socket) === false) {
                        throw new IOException('Cannot send message body!');
                    }
                    if (fwrite($this->_socket, "\r\n") === false) {
                        throw new IOException('Cannot send message body!');
                    }
                }else{
                    if (fwrite($this->_socket,
                        $this->messageBody."\r\n") === false) {
                        throw new IOException('Cannot send message body!');
                    }
                }
            }
            stream_socket_shutdown($this->_socket, STREAM_SHUT_RDWR);
        }
    }

    private function _headers()
    {
        $headers = array();
        self::$_log->debug(__METHOD__.' called');
        self::$_log->debug('Creating header lines');
        foreach ($this->headers as $key=>$value) {
            self::$_log->debug(sprintf('%s:%s',$key,$value));
            array_push($headers, sprintf('%s:%s',$key,$value));
        }
        $headers = implode("\r\n", $headers);
        return $headers;
    }
}