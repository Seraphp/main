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
require_once 'Comm/Request.interface.php';
require_once 'Log/LogFactory.class.php';
require_once 'ObservableListener.interface.php';
require_once 'Exceptions/HttpException.class.php';
require_once 'Exceptions/SocketException.class.php';
require_once 'Exceptions/IOException.class.php';
require_once 'HttpFactory.class.php';
/**
 * Class represents an HTTP request, no matter
 * of the usage: sending or receiving it.
 *
 * @package Comm
 * @subpackage Http
 * @todo Test HTTPRequest class
 */
class HttpRequest implements Request, Observable
{
    const REQ_TOSEND = false;
    const REQ_RECEIVED = true;
    private static $_log = null;
    public $timeout = 30;
    /**
     * Holds sanitized POST parameters in array
     * @var array
     */
    public $postParams = array();
    /**
     * Holds sanitized GET parameters in array
     * @var array
     */
    public $getParams = array();
    /**
     * Holds sanitized COOKIE data in array
     * @var array
     */
    public $cookies = array();
    /**
     * Holds raw http header string
     * @var string
     */
    public $httpRawHeaders = '';
    /**
     * Holds parsed HTTP headers in array
     * @var array
     */
    public $httpHeaders = array();
    /**
     * Holds HTTP body (if any) in string
     * @var string
     */
    public $message = '';
    /**
     * Stores request type like HEAD, GET, POST, etc.
     * @var string
     */
    public $method = 'GET';

    public $referer = '';

    public $url = '';

    public $httpVersion = '1.x';

    /**
     * @var string
     */
    public $contentType = 'text/html';
    /**
     * If this request is received(true), or created to be sent(FALSE).
     * @var boolean
     */
    public $isReceived = false;

    /**
     * Communication socket
     * @var null|resource
     */
    private $_socket = null;

    /**
     * Internal buffer for reading socket
     * @var string
     */
    private $_buffer = null;

    /**
     * Array of objects listening for status change
     * @var array
     */
    private $_listeners = array();

    /**
     * Constructor method
     *
     * Not giving an already opened socket means that we will send this request,
     * instead of receiving a request through that socket.
     *
     * @param $sock (optional)
     * @return HttpRequest
     */
    public function __construct( $sock = null )
    {
        self::$_log = LogFactory::getInstance();
        self::$_log->debug(__METHOD__.' called');
        if ( $sock !== null && is_resource($sock) ) {
         //If parameter is a resource, it means the class will represent a
         //received request
            $this->_socket = $sock;
            $this->isReceived = self::REQ_RECEIVED;
        } else {
             $this->isReceived = self::REQ_TOSEND;
        }
        $this->_parse();
    }

    /**
     * Parses already arrived data
     *
     * While parsing it is notifies all listeners of the arrived amount of data
     *
     * @throws IOException
     * @return void
     */
    protected function _parse()
    {
        self::$_log->debug(__METHOD__.' called');
        if ($this->isReceived === self::REQ_RECEIVED) {
            $read = array($this->_socket);
            if (stream_select($read,
                     $write = null, $except = null, $this->timeout, 200) < 1) {
                throw new IOException('Connection timed out!');
            }
            self::$_log->debug('Data arriving on socket');
            while ($this->_buffer =
                    fread($this->_socket, 8)) {
                if ($this->_buffer === false || $this->_buffer === '') {
                     $this->_save();
                     continue;
                }
                $this->_save();
                if ($this->httpHeaders !== array()) {
                //We notify only when all headers are received
                    $this->notify();
                }
            }
            $this->message = trim($this->message);
            //Body also arrived, searching for post
            if ( $this->method == 'POST' ) {
                $this->postParams = $this->_params2array($this->message);
            }
        }
    }

    /**
     * Save buffer to message and examin if headers arrived completly
     *
     * If we detect a double line end ('\r\n\r\n') in the message received
     * we separate the raw headers from the message.
     *
     * @return void
     */
    protected function _save()
    {
        $this->message .= $this->_buffer;
        if (strpos($this->message, "\r\n\r\n") !== false &&
            $this->httpHeaders === array()) {
                //if received first time a datachunk containing an empty line,
                //we arrived at the end of the http header
            list($this->httpRawHeaders, $this->message) =
                preg_split('/(\r\n){2}/m', $this->message);
            $this->httpRawHeaders = trim($this->httpRawHeaders);
            $this->httpHeaders = explode("\r\n", $this->httpRawHeaders);
            $this->_processHeaders();
        }
    }

    /**
     * Process arrived headers
     *
     * @return void
     */
    protected function _processHeaders()
    {
        self::$_log->debug(__METHOD__.' called');
        $headers = array();
        //First line in the array should be the reqest line,
        // like 'GET /index.html HTTP/1.1'
        $requestLine = array_shift($this->httpHeaders);
        list ($this->method, $this->url, $this->httpVersion) =
            explode(' ', $requestLine);
        $this->httpVersion = substr($this->httpVersion, -3);
        foreach ($this->httpHeaders as $row) {
            $item = explode(':', $row, 2);
            if (array_key_exists($item[0], $headers)) {
                $headers[$item[0]] .= ';'.trim($item[1]);
            } else {
                $headers[$item[0]] = trim($item[1]);
            }
        }
        $this->httpHeaders = $headers;
        self::$_log->debug('setting up get, cookies');
        if (array_key_exists('Cookie', $this->httpHeaders)) {
            $this->cookies =
                HttpFactory::getCookies($this->httpHeaders['Cookie']);
        }
        self::$_log->debug('processing GET parameters');
        if ($pos = strpos($this->url, '?')) {
            $this->getParams = $this->_params2array(substr($this->url, $pos+1));
        }
        self::$_log->debug('Setting Referer if any');
        if (array_key_exists('Referer', $this->httpHeaders) ) {
            $this->referer = $this->httpHeaders['Referer'];
        }
    }

    /**
     * Parses urlencoded parameter string into hash array
     *
     * @param string $str
     * @return array
     */
    private function _params2array($str)
    {
        self::$_log->debug(__METHOD__.' called');
        $rows = explode('&', urldecode($str));
        $params = array();
        $itemNum = count($rows);
        for ($idx = 0; $idx < $itemNum; $idx++) {
            $param = explode('=', $rows[$idx], 2);
            unset($rows[$idx]);
            $params[$param[0]] = $param[1];
        }
        return $params;
    }

    /**
     * Parses hash array into urlencoded string
     *
     * @param array $params  Parameters to encode
     * @param string $parentKey  (optional) Key to use to encode an
     * array (deafult NULL)
     * @param string $sep  (optional) Separator string to pőut between encoded
     * keys (default '&')
     * @return string
     */
    private function _array2params($params, $parentKey = null, $sep = '&')
    {
        self::$_log->debug(__METHOD__.' called');
        $items = array();
        foreach ( $params as $key => $value ) {
            if ( is_array($value) ) {
                array_push($items, $this->_array2params($value, $key, $sep));
            } else {
                if ( $parentKey !== null ) {
                    array_push($items,
                           sprintf('%s[%s]=%s', $parentKey, $key, $value));
                } else {
                    array_push($items, sprintf('%s=%s', $key, $value));
                }
            }
        }
        return implode($sep, $items);
    }


    /**
     * @return real  Ratio of the already received content against the full
     * content length.
     */
    public function getState()
    {
        return strlen($this->message);
    }

    public function notify()
    {
        foreach ($this->_listeners as $listener) {
            $listener->update($this);
        }
    }

    public function attach(Listener $listener)
    {
        $objName = $listener->getName();
        if (!array_key_exists($objName, $this->_listeners)) {
            $this->_listeners[$objName] = $listener;
            return $objName;
        } else {
            return false;
        }
    }

    public function detach($listenerName)
    {
        if (array_key_exists($listenerName, $this->_listeners)) {
            unset($this->_listeners[$listenerName]);
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return HttpResponse
     * @throws HttpException
     */
    public function send()
    {
        self::$_log->debug(__METHOD__.' called');
        if ($this->isReceived === self::REQ_TOSEND) {
            $curlObj = curl_init($this->url);
            $options = array(
                CURLOPT_CUSTOMREQUEST =>
                    (empty($this->method))?'GET':$this->method,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER =>true,
                CURLOPT_FRESH_CONNECT =>true
            );
            //Setting HTTP version for Curl
            switch ($this->httpVersion) {
                case '1.0':
                    $options[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_1_0;
                    break;
                case '1.1':
                    $options[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_1_1;
                    break;
                default:
                    $options[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_NONE;
                    break;
            }
            //Setting Cookies for curl
            if ($this->cookies !== array()) {
                $setCookieStr = '';
                foreach ($this->cookies as $cookie) {
                    $setCookieStr .= $cookie->__toString().';';
                }
                $options[CURLOPT_COOKIE] = $setCookieStr;
                unset($setCookieStr);
            }
            //Setting up POST parameters for curl
            if ($this->postParams !== array()) {
                $options[CURLOPT_POSTFIELDS] =
                   $this->_array2params($this->postParams);
            }
            //Setting up GET parameters for curl
            if ($this->getParams !== array() ) {
                $sep = (strpos($this->url, '?') !== false)?'&':'?';
                $options[CURLOPT_URL] = $this->url.$sep.
                     urlencode($this->_array2params($this->getParams));
            }
            curl_setopt_array($curlObj, $options);
            $response = curl_exec($curlObj);
            if ($response === false) {
                throw new HttpException(curl_error($curlObj));
            }
            curl_close($curlObj);
            $resp = HttpFactory::create('response', null);
            $resp->parse($response);
            return $resp;
        } else throw new HttpException(
                'HttpRequest instance is received not to be send out!'
            );
    }

    /**
     * Creates a HttpResponse class, according to the Requests paramteres
     *
     * @return HttpResponse
     * @throws HttpException
     */
    public function respond($msg, $settings = null)
    {
        self::$_log->debug(__METHOD__.' called');
        if ($this->isReceived === self::REQ_RECEIVED) {
            $response = HttpFactory::create('response',
                $this->_socket,
                $settings);
            $response->messageBody = $msg;
            return $response;
        } else {
            throw new HttpException(
                'HttpRequest instance is to be send out: cannot be responded!'
            );
        }
    }
}
