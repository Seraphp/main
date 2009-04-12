<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
require_once 'PHPUnit/Framework.php';
require_once 'Comm/Http/HttpResponse.class.php';
/**
 * Class documentation
 */
class HttpResponseTest extends PHPUnit_Framework_TestCase{

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
        $this->assertEquals(0, $cookie->expireOn);
        $this->assertEquals('/', $cookie->path);
        $this->assertEquals('null', $cookie->domain);
        $this->assertFalse($cookie->secure);
        $this->assertFalse($cookie->onlyHTTP);
    }

    function testToString()
    {
        $cookie = new HttpCookie($this->_name);
        $this->asserEquals(sprintf('Set-Cookie: %s=%s; path=/;'.
            'expires=%s', $this->_name, $this->_value, 0),
            $cookie->__toString());
    }
}