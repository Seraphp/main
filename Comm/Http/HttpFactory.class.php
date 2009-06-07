<?php
/**
 * Contains HTTPFactory class implementation.
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @copyright Copyright (c) 2009, Peter Nagy
 * @package Comm
 * @subpackage Http
 * @filesource
 * @todo Implement HttpFactory class
 */
/***/
//namespace Seraphp\Comm\Http
require_once 'Comm/Http/HttpCookie.class.php';
require_once 'Comm/Http/HttpRequest.class.php';
require_once 'Comm/Http/HttpResponse.class.php';
/**
 * Generates HTTP related classes
 *
 * @package Comm
 * @subpackage Http
 */
class HttpFactory
{

    private static $_log;

    public static $httpStatuses = array(
            100 => 'Continue',
            101 => 'Switching protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use proxy',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            500 => 'Internal Server Error',
        );
    /**
     * @param array $array  An array of cookie settings
     * @return array  An array of HttpCookie objects
     */
    public static function getCookies($array)
    {
        self::$_log = LogFactory::getInstance();
        self::$_log->debug(__METHOD__.' called');
        $result = array();
        foreach ($array as $cookieParams) {
            extract($cookieParams);
            $result[] = new HttpCookie($name,
                                        $value,
                                        $expireOn,
                                        $path,
                                        $domain,
                                        $secure,
                                        $onlyHTTP);
        }
        return $result;
    }

    /**
     * @param $type Message type, either Request or Response
     * @param null|socket $sock   Socket
     * @param null|array $settings
     * @return HttpRequest|HttpResponse|null
     */
    public static function create($type, $socket = null, $settings = null)
    {
        self::$_log = LogFactory::getInstance();
        self::$_log->debug(__METHOD__.' called');
        switch ($type)
        {
            case 'request':
                self::$_log->debug('Creating HTTP Request');
                return new HttpRequest($socket);
                break;
            case 'response':
                self::$_log->debug('Creating HTTP Response');
                return new HttpResponse($socket);
                break;
            case 'cookie':
                self::$_log->debug('Creating HTTP Cookie');
                return self::getCookies($settings);
                break;
            default:
                return null;
                break;
        }
    }

    /**
     * Returns HTTP status message if it is defined in class variable
     *
     * Returns NULL if not found.
     *
     * @param integer $code  HTTP status code
     * @return string|null
     */
    public static function getHttpStatus($code)
    {
        self::$_log = LogFactory::getInstance();
        self::$_log->debug(__METHOD__.' called');
        if (array_key_exists($code, self::$httpStatuses)) {
            return self::$httpStatuses[$code];
        } else {
            return null;
        }
    }
}