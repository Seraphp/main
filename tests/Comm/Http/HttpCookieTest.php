<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
require_once 'PHPUnit/Framework.php';
require_once 'Comm/Http/HttpCookie.class.php';
/**
 * Class documentation
 */
class HttpCookieTest extends PHPUnit_Framework_TestCase
{

    private $_name = '';
    private $_value = '';

    function setUp()
    {
        $this->_name = 'testCookie';
        $this->_value = '42';
    }

    function testConstructor()
    {
        $cookie = new HttpCookie($this->_name);
        $this->assertEquals($this->_name, $cookie->name);
        $this->assertFalse($cookie->value);
        $this->assertEquals(null, $cookie->expireOn);
        $this->assertEquals('/', $cookie->path);
        $this->assertEquals(null, $cookie->domain);
        $this->assertFalse($cookie->secure);
        $this->assertFalse($cookie->onlyHTTP);
    }

    function testToString()
    {
        $cookie = new HttpCookie(
            $this->_name, $this->_value, null, '/', 'localhost', true
        );
        $this->assertEquals(
            sprintf(
                'Set-Cookie:%s=%s;Max-Age=%s;Path=/;Domain=%s;Secure',
                $this->_name, $this->_value, 0, 'localhost'
            ),
            $cookie->__toString()
        );
    }
}