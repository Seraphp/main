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

    public function __construct( $name, $value=false, DateTime $expireOn = null,
        $path='/', $domain=null, $secure=false, $onlyHTTP=false)
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
        $details = array();
        if (isset($this->expireOn)) {
            $details[] = 'Max-Age='.$date->format('U')-time();
        } else {
            $details[] = 'Max-Age=0';
        }
        if (isset($this->path)) {
            $details[] = 'Path='.$this->path;
        }
        if (isset($this->domain)) {
            $details[] = 'Domain='.$this->domain;
        }
        if ($this->secure) {
            $details[] = 'Secure';
        }
        return sprintf(
            'Set-Cookie:%s=%s;%s',
            $this->name,
            $this->value,
            implode(';', $details)
        );
    }
}