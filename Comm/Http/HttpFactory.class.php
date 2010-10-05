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
namespace Seraphp\Comm\Http;
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
     * @param array|string $array  An array of cookie settings or a string of
     * Cookie header
     * @return array  An array of HttpCookie objects
     */
    public static function getCookies($param)
    {
        self::$_log = \Seraphp\Log\LogFactory::getInstance();
        $result = array();
        if (is_string($param)) {
            //We have an http Cookie header to parse
            $param = array(HttpFactory::parseCookieString($param));
        } elseif (isset($param['name'])) {
            //We have a single cookie request
            $param = array($param);
        }
        foreach ($param as $cookieParams) {
            $name = null;
            $value = null;
            $expireOn = null;
            $path = null;
            $domain = null;
            $secure = null;
            $onlyHTTP = null;
            extract($cookieParams, EXTR_IF_EXISTS);
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
        self::$_log = \Seraphp\Log\LogFactory::getInstance();
        switch ($type)
        {
            case 'request':
                $obj = new HttpRequest($socket);
                if ($settings !== null) {
                    foreach ($settings as $key => $value) {
                        $obj->$key=$value;
                    };
                }
                break;
            case 'response':
                $obj = new HttpResponse($socket);
                if ($settings !== null) {
                    foreach ($settings as $key => $value) {
                        $obj->$key=$value;
                    };
                }
                break;
            case 'cookie':
                $obj = self::getCookies($settings);
                break;
            default:
                $obj = null;
                break;
        }
        return $obj;
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
        self::$_log = \Seraphp\Log\LogFactory::getInstance();
        if (array_key_exists($code, self::$httpStatuses)) {
            return self::$httpStatuses[$code];
        } else {
            return null;
        }
    }

    /**
     * Parses a HTTP cookies string into an array.
     *
     * Parses the fields from the string. Any key which is not in extras
     * (above 'expireOn','path', 'domain', 'secure', 'onlyHTTP') will be droped.
     *
     * @param string $str  HTTP cookie string to parse
     * @param array $extras  Extra fields in the cookie to parse
     * @return array
     */
    public static function parseCookieString($str, $extras = array())
    {
        $parts = array('expireOn','path', 'domain', 'secure', 'onlyHTTP');
        $parts = array_merge($parts, $extras);
        $result = array();
        $props = explode(';', $str);
        foreach ($props as $prop) {
            $values = explode('=', trim($prop));
            if (array_key_exists($values[0], $parts)) {
                $result[trim($values[0])] = trim($values[1]);
            } elseif (!array_key_exists('name', $result)) {
                $result['name'] = $values[0];
                $result['value'] = $values[1];
            }
        }
        return $result;
    }
}