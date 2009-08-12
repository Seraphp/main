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
     * Method to send out HTTP response
     *
     * @return void
     * @throws IOException
     */
    public function send()
    {
        self::$_log->debug(__METHOD__.' called');
        if ($this->toBeSend) {
            //Creating Status line
            $this->statusLine = sprintf('HTTP/%s %d %s',
                             $this->httpVersion,
                             $this->statusCode,
                             HttpFactory::getHttpStatus($this->statusCode));
            $buffer = $this->statusLine."\r\n";
            $buffer .= $this->_getHeaders()."\r\n";
            self::$_log->debug('Sending out head part');
            if (fwrite($this->_socket, $buffer."\r\n") === false) {
                throw new IOException('Cannot send out header part');
            }
            if (!empty($this->messageBody)) {
                $this->_sendBody();
            }
            stream_socket_shutdown($this->_socket, STREAM_SHUT_RDWR);
        }
    }

    private function _configHeaders()
    {
        $this->headers['Date'] = date(DATE_RFC1123);
        //Keep-Alive feature not implemented yet
        $this->headers['Connection'] = 'Closed';
        //If a file is to be sent in the message bdy
        if (is_resource($this->messageBody)) {
            $data = array_merge(fstat($this->messageBody),
                stream_get_meta_data($this->messageBody));
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
        self::$_log->debug('Sending message body');
        if (is_resource($this->messageBody)) {
            if (stream_copy_to_stream($this->messageBody,
                $this->_socket) === false) {
                throw new IOException('Cannot send message body!');
            }
            if (fwrite($this->_socket, "\r\n") === false) {
                throw new IOException('Cannot send message body!');
            }
        } else {
            if (fwrite($this->_socket,
                $this->messageBody."\r\n") === false) {
                throw new IOException('Cannot send message body!');
            }
        }
    }

    private function _getHeaders()
    {
        self::$_log->debug(__METHOD__.' called');
        self::$_log->debug('Creating header lines');
        $headers = array();
        $this->_configHeaders();
        foreach ($this->headers as $key=>$value) {
            self::$_log->debug(sprintf('%s:%s', $key, $value));
            array_push($headers, sprintf('%s:%s', $key, $value));
        }
        $headers = implode("\r\n", $headers);
        return $headers;
    }
}