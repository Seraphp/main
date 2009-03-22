<?php
/**
 * Contains the class HTTPCookie for representing a web cookie
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
/**
 * Class representing an HTTP cookie
 *
 * @package Comm
 * @subpackage Http
 */
class HttpCookie
{
    public $name = '';
    public $value = null;
    public $expireOn = null;
    public $path = null;
    public $domain = null;
    public $secure = false;
    public $onlyHTTP = false;

    public function __construct( $name, $value=false, $expireOn = 0, $path='/',
        $domain='null', $secure = false, $onlyHTTP=false)
    {
        $this->name = $name;
        $this->value = $value;
        $this->expireOn = $expireOn;
        $this->path = $path;
        $this->domain = $domain;
        $this->secure = $secure;
        $this->onlyHTTP = $onlyHTTP;
    }

    public function __toString()
    {
        ob_start();
        setcookie(
            $this->name,
            $this->value,
            $this->expireOn,
            $this->path,
            $this->domain,
            $this->onlyHTTP
        );
        $cookieStr = ob_get_contents();
        ob_end_clean();
        return $cookieStr;
    }
}