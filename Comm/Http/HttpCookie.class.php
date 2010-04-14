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
    /**
     * Constans for HttpCookie::$type. Means to use Set-Cookie header
     */
    const COOKIE_TYPE_RFC2109 = 1;
    /**
     * Constans for HttpCookie::$type. Means to use Set-Cookie2 header
     */
    const COOKIE_TYPE_RFC2965 = 2;

    public $type = COOKIE_TYPE_RFC2109;
    public $name = '';
    public $value = null;
    public $expireOn = null;
    public $path = null;
    public $domain = null;
    public $comment = null;
    public $commentUrl = null;
    public $discard = false;
    public $secure = false;
    public $porlist = array();
    public $version = 1;

    public function __construct($name, $value=false, DateTime $expireOn = null,
        $path='/', $domain=null, $secure=false, $comment=null,
        $commentUrl = null, $discard = false, $portList=array())
    {
        $this->name = $name;
        $this->value = $value;
        $this->expireOn = $expireOn;
        $this->path = $path;
        $this->domain = $domain;
        $this->secure = $secure;
        $this->comment = $comment;
        $this->commentUrl = $commentUrl;
        $this->discard = $discard;
        $this->portList = $portList;
    }

    public function __toString()
    {
        $details = array();
        if (isset($this->expireOn)) {
            if ($this->type == COOKIE_TYPE_RFC2109) {
                $details[] = 'Max-Age=' .
                    $this->expireOn->format(DateTime::COOKIE);
            } else {
                $details[] = 'Max-Age=' .
                    $this->expireOn->diff(new DateTime('now'))->format('%r%s');
            }
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
        if ($this->discard) {
            $details[] = 'Discard';
        }
        return sprintf(
            '%s:%s=%s;%s',
            ($this->type == COOKIE_TYPE_RFC2965)?'Set-Cookie2':'Set-Cookie',
            $this->name,
            $this->value,
            implode(';', $details)
        );
    }
}