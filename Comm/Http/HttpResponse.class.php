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
namespace Seraphp\Comm\Http;
//require_once 'ObserverListener.interface.php';
require_once 'Log/LogFactory.class.php';
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
    public $rawHeaders = '';
    public $headers = array();
    public $cookies = array();
    public $contentType = 'text/plain';
    public $statusCode = 200;
    public $messageBody = null;
    public $httpVersion = '1.x';
    private $_socket = null;
    public $toBeSend = true;
    public $timeout = 30;

    public function __construct($socket = null)
    {
        self::$_log = \Seraphp\Log\LogFactory::getInstance();
        if ($socket !== null && is_resource($socket)) {
            $this->_socket = $socket;
            $this->toBeSend = true;
            $this->headers['Server'] = 'Seraphp 0.1';
        } else {
            $this->toBeSend = false;
        }
    }

    /**
     * Method to send out HTTP response
     *
     * @return void
     * @throws IOException
     */
    public function send()
    {
        if ($this->toBeSend) {
            //Creating Status line
            $this->statusLine = sprintf(
                'HTTP/%s %d %s',
                $this->httpVersion,
                $this->statusCode,
                HttpFactory::getHttpStatus($this->statusCode)
            );
            $buffer = $this->statusLine."\r\n";
            $buffer .= $this->_getHeaders()."\r\n";
         //if (stream_socket_sendto($this->_socket, $buffer."\r\n") === false) {
            if (socket_write($this->_socket, $buffer."\r\n") === false) {
                throw new \Seraphp\Exceptions\IOException('Cannot send out header part');
            }
            if (!empty($this->messageBody)) {
                try {
                    $this->_sendBody();
                } catch (\Seraphp\Exceptions\IOException $e) {
                    self::$_log->warn($e->getMessage());
                }
            }
            //stream_socket_shutdown($this->_socket, STREAM_SHUT_RDWR);
            @socket_shutdown($this->_socket, 2);
            @socket_close($this->_socket);
        }
    }

    public function parse($msg)
    {
        $lines = explode("\r\n\r\n", $msg, 2);
        $this->rawHeaders = $lines[0];
        if (sizeof($lines)>1) {
            $this->messageBody = trim($lines[1]);
        }
        unset($lines);
        $headers = explode("\r\n", $this->rawHeaders);
        //Parsing first line
        preg_match('/^HTTP\/(\d\.\d) (\d*) (.*)$/', $headers[0], $matches);
        $this->httpVersion = $matches[1];
        $this->statusCode = $matches[2];
        $this->statusLine = $matches[3];
        unset($headers[0]);
        foreach ($headers as $header) {
            list($key, $value) = explode(':', $header, 2);
            $this->headers[trim($key)] = trim($value);
        }
        if (isset($this->headers['Content-Type'])) {
            $this->contentType = $this->headers['Content-Type'];
        }
    }

    private function _configHeaders()
    {
        $this->headers['Date'] = date(DATE_RFC1123);
        //Keep-Alive feature not implemented yet
        $this->headers['Connection'] = 'Closed';
        //If a file is to be sent in the message bdy
        if (is_resource($this->messageBody)) {
            $data = array_merge(
                fstat($this->messageBody),
                stream_get_meta_data($this->messageBody)
            );
            $this->headers['Content-Length'] = $data['size'];
            $this->headers['Last-Modified'] =
                date(DATE_RFC1123, $data['mtime']);
            $this->headers['Etag'] = md5_file($data['uri']);
        } else {
            $this->headers['Content-Length'] = strlen($this->messageBody);
            $this->headers['Etag'] = md5($this->messageBody);
        }
        $this->headers['Content-Type'] = $this->contentType;
    }

    private function _sendBody()
    {
        if (is_resource($this->messageBody)) {
        /*if (stream_copy_to_stream($this->messageBody, $this->_socket) === false) {
            throw new IOException('Cannot send file in body!');
        }*/
            while (!feof($this->messageBody)) {
                socket_write(
                    $this->_socket,
                    fread($this->messageBody, 1024),
                    1024
                );
            }
        } else {
            if (socket_write($this->_socket, $this->messageBody) === false) {
                throw new \Seraphp\Exceptions\IOException(
                    'Cannot send message body!'
                );
            }
        }
        socket_write($this->_socket, "\r\n");
    }

    private function _getHeaders()
    {
        $headers = array();
        $this->_configHeaders();
        foreach ($this->headers as $key=>$value) {
            array_push($headers, sprintf('%s:%s', $key, $value));
        }
        $headers = implode("\r\n", $headers);
        return $headers;
    }
}
