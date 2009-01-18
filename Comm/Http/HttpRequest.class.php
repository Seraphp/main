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
require_once 'Comm/Request.interface.php';
require_once 'ObserverListener.interface.php';
/**
 * Class represents an HTTP request, no matter
 * of the usage: sending or receiving it.
 * @package Comm
 * @subpackage Http
 * @todo Test HTTPRequest class
 */
class HttpRequest implements Request, Listener{

    const REQ_TOSEND = false;
    const REQ_RECEIVED = true;
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
    public $method = '';

    public $referer = '';

    public $url = '';

    public $httpVersion = '';

    /**
     * @var string
     */
    public $contentType = '';
    /**
     * If this request is received(true), or created to be sent(FALSE).
     * @var boolean
     */
    public $isReceived = false;

    /**
     * Communication socket
     * @var null|resource
     */
    private $socket = null;

    /**
     * Internal buffer for reading socket
     * @var string
     */
    private $buffer = null;

    /**
     * Array of objects listening for status change
     * @var array
     */
    private $observers = array();

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
        if ( $sock !== null && is_resource( $sock ) )
        {//If parameter is a resource, it means the class will represent a
         //received request
            $this->socket = $sock;
            $this->isReceived = self::REQ_RECEIVED;
        }
        else
        {
             $this->isReceived = self::REQ_TOSEND;
        }
        $this->parse();
    }

    protected function parse()
    {
        if ( $this->isReceived === self::REQ_RECEIVED )
        {
           while ( $this->buffer = socket_read( $this->socket ) )
           {
               $this->save();
               if ( $this->httpHeaders !== array() )
               {//We notify only when all headers are received
                   $this->notify();
               }
           }
           //Body also arrived, searching for post
           if ( $this->method == 'POST' )
           {
               $this->postParams = $this->params2array($this->message);
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
    protected function save()
    {
        $this->message .= $this->buffer;
        if ( $this->httpHeaders === array() && strpos( $this->message, "\r\n\r\n") !== false )
        {//if received first time a datachunk containing an empty line,
         //we arrived at the end of the http header
            list( $this->message, $this->httpRawHeaders ) = preg_split( '/(\r\n){2}/m', $this->message );
            $this->httpRawHeaders = trim( $this->httpRawHeaders );
            $this->httpHeaders = explode( "\r\n", $this->httpRawHeaders );
            $this->processHeaders();
        }
    }

    /**
     * Process arrived headers
     *
     * @return void
     */
    protected function processHeaders()
    {
        $headers = array();
        $requestLine = array_shift( $this->httpHeaders );
        list ( $this->method, $this->url, $this->httpVersion ) = explode( ' ', $requestLine );
        $this->httpVersion = substr( $this->httpVersion, -3 );
        foreach ( $this->httpHeaders as $row )
        {
            $item = explode ( ':', $row, 2);
            if ( array_key_exists( $item[0], $headers ) )
            {
                $headers[ $item[0] ] .= ';'.$item[1];
            }
            else
            {
                $headers[ $item[0] ] = $item[1];
            }
        }
        $this->httpHeaders = $headers;
        //set up get, cookies (post will be in the body)
        if ( array_key_exists( 'Cookie', $this->httpHeaders ) )
        {
            $this->cookies = explode( ';', $this->httpHeaders[ 'Cookies' ] );
            //@todo: create CookieFactory and the Cookie class
            $this->cookies = HttpFactory::getCookies($this->cookies);
        }
        //processing GET parameters
        if ( $pos = strpos( $this->url, '?' ) )
        {
        	$this->getParams = $this->params2array( substr( $this->url, $pos+1 ) );
        }
        //Setting Referer
        if (array_key_exists( 'Referer', $this->httHeaders ) )
        {
            $this->referer = $this->httHeaders['Referer'];
        }
    }

    /**
     * Parses urlencoded parameter string into hash array
     *
     * @param string $str
     * @return array
     */
    private function params2array($str)
    {
        $rows = explode( '&', urldecode( $str ) );
        $params = array();
        $itemNum = count($rows);
        for( $idx = 0; $idx<$itemNum; $idx++ )
        {
            $param = explode( '=', $rows[ $idx ], 2 );
            unset( $rows[ $idx ] );
            $params[ $param[0] ] = $param[1];
        }
        return $params;
    }

    /**
     * Parses hash array into urlencoded string
     *
     * @param array $params  Parameters to encode
     * @param string $parentKey  (optional) Key to use to encode an array (deafult NULL)
     * @param string $sep  (optional) Separator string to pőut between encoded keys (default '&')
     * @return string
     */
    private function array2params($params, $parentKey = null, $sep = '&')
    {
        $items = array();
        foreach ( $params as $key => $value )
        {
        	if ( is_array( $value ) )
        	{
        	    array_push( $items, $this->array2params($value, $key, $sep) );
        	}
        	else
        	{
        	    if ( $parentKey !== null )
        	    {
        	        array_push( $items, sprintf( '%s[%s]=%s' ), $parentKey, $key, $value );
        	    }
        	    else
        	    {
        	        array_push( $items, sprintf( '%s=%s' ), $key, $value );
        	    }
        	}
        }
        return implode( $sep, $items );
    }


    /**
     * @return real  Ratio of the already received content against the full content length.
     */
    public function getState()
    {
        return $this->fullLength / count( $this->message );
    }

    public function notify()
    {
        foreach ( $this->observers as $observer )
        {
            $observer->update( $this );
        }
    }

    public function attach( Observer $observer )
    {
        if ( !array_key_exists( $observer->getName() ) )
        {
            return $this->observers[ $observer->getName() ] = $observer;
        }
        else
        {
            return false;
        }
    }

    public function detach( Observer $observer )
    {
        if ( array_key_exists($observer->getName()) )
        {
            unset ( $this->observers[ $observer->getName() ] );
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * @return HttpResponse
     * @throws HttpException
     */
    public function send()
    {
        //@todo Implement HttpRequest sending
        if ( $this->isReceived === self::REQ_TOSEND )
        {
            $curlObj = curl_init( $this->url );
            $options = array(
                CURLOPT_CUSTOMREQUEST => ( empty( $this->method) )?'GET':$this->method,
                CURLOPT_RETURNTRANSFER => true,
            );
            //Setting HTTP version for Curl
            switch( $this->httpVersion )
            {
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
            if ($this->cookies !== array() )
            {
            	$setCookieStr = '';
                foreach( $this->cookies as $cookie )
            	{
            	    $setCookieStr .= $cookie->__toString().';';
            	}
            	$options[CURLOPT_COOKIE] = $setCookieStr;
            	unset($setCookieStr);
            }
            //Setting up POST parameters for curl
            if ($this->postParams !== array() )
            {
            	$options[CURLOPT_POSTFIELDS] = $this->array2params($this->postParams);
            }
            //Setting up GET parameters for curl
            if ($this->getParams !== array() )
            {
            	if ( strpos( $this->url, '?' ) !== false )
            	{
            		$options[CURLOPT_URL] = $this->url . urlencode( '&'. $this->array2params($this->getParams) );
            	}
            	else
            	{
            	    $options[CURLOPT_URL] = $this->url .'?'. urlencode( $this->array2params($this->getParams) );
            	}
            }
            curl_setopt_array( $curlObj, $options);
            $response = curl_exec($curlObj);
            curl_close($curlObj);
            return $response;
        }
        else throw new HttpException('HttpRequest instance is received not to be send out!');
    }
}

class HttpException extends Exception{}
?>