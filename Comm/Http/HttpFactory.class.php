<?php
/**
 * File documentation
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
require_once 'Comm/Http/HttpCookie.class.php';
require_once 'Comm/Http/HttpRequest.class.php';
require_once 'Comm/Http/HttpResponse.class.php';
/**
 * Class documentation
 * @package Comm
 * @subpackage Http
 */
class HttpFactory{

    /**
     * @param array $array  An array of cookie settings
     * @return array  An array of HttpCookie objects
     */
    public function getCookies($array)
    {

    }

    /**
     * @param $type Message type, either Request or Response
     * @param null|socket $sock   Socket
     * @return HttpRequest|HttpResponse
     */
    public function getMessage($type, $sock = null, $settings = null)
    {

    }

}
?>