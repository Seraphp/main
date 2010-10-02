<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */

require_once 'Comm/Http/HttpFactory.class.php';
/**
 * Class documentation
 */
class HttpFactoryTest extends PHPUnit_Framework_TestCase
{

    private $_cookies = array();

    function testCreateRequest()
    {
        $res = HttpFactory::create("request", array("method"=>"get"));
        $this->assertType(
            PHPUnit_Framework_Constraint_IsType::TYPE_OBJECT, $res
        );
        $this->assertThat($res, $this->isInstanceOf('HttpRequest'));
    }

    function testCreateResponse()
    {
        $res = HttpFactory::create("response", array("statusCode"=>"404"));
        $this->assertType(
            PHPUnit_Framework_Constraint_IsType::TYPE_OBJECT, $res
        );
        $this->assertThat($res, $this->isInstanceOf('HttpResponse'));
    }

    function testCreateRequestWithSettings()
    {
        $res = HttpFactory::create(
            "request", array('contentType'=>'text/html')
        );
        $this->assertType(
            PHPUnit_Framework_Constraint_IsType::TYPE_OBJECT, $res
        );
        $this->assertThat($res, $this->isInstanceOf('HttpRequest'));
    }

    function testCreateResponseWithSettings()
    {
        $res = HttpFactory::create(
            "response", array('contentType'=>'text/html')
        );
        $this->assertType(
            PHPUnit_Framework_Constraint_IsType::TYPE_OBJECT, $res
        );
        $this->assertThat($res, $this->isInstanceOf('HttpResponse'));
    }

    function testCreateCookies()
    {
        $this->_cookies = array();
        array_push($this->_cookies, array('name'=>'testCookie'));
        $res = HttpFactory::create("cookie", null, $this->_cookies);
        $this->assertType(
            PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $res
        );
        $this->assertThat($res[0], $this->isInstanceOf('HttpCookie'));
    }

    function testGetValidHttpStatusCodes()
    {
        $this->assertEquals('Continue', HTTPFactory::getHttpStatus(100));
    }

    function testGetNotValidHttpStatusCodes()
    {
        $this->assertEquals(null, HTTPFactory::getHttpStatus(900));
    }
}