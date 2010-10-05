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
        $res = \Seraphp\Comm\Http\HttpFactory::create(
            'request', array('method'=>'get')
        );
        $this->assertType(
            PHPUnit_Framework_Constraint_IsType::TYPE_OBJECT, $res
        );
        $this->assertThat(
            $res, $this->isInstanceOf('\Seraphp\Comm\Http\HttpRequest')
        );
    }

    function testCreateResponse()
    {
        $res = \Seraphp\Comm\Http\HttpFactory::create(
            'response', array('statusCode'=>'404')
        );
        $this->assertType(
            PHPUnit_Framework_Constraint_IsType::TYPE_OBJECT, $res
        );
        $this->assertThat(
            $res, $this->isInstanceOf('\Seraphp\Comm\Http\HttpResponse')
        );
    }

    function testCreateRequestWithSettings()
    {
        $res = \Seraphp\Comm\Http\HttpFactory::create(
            'request', array('contentType'=>'text/html')
        );
        $this->assertType(
            PHPUnit_Framework_Constraint_IsType::TYPE_OBJECT, $res
        );
        $this->assertThat(
            $res, $this->isInstanceOf('\Seraphp\Comm\Http\HttpRequest')
        );
    }

    function testCreateResponseWithSettings()
    {
        $res = \Seraphp\Comm\Http\HttpFactory::create(
            'response', array('contentType'=>'text/html')
        );
        $this->assertType(
            PHPUnit_Framework_Constraint_IsType::TYPE_OBJECT, $res
        );
        $this->assertThat(
            $res, $this->isInstanceOf('\Seraphp\Comm\Http\HttpResponse')
        );
    }

    function testCreateCookies()
    {
        $this->_cookies = array();
        array_push($this->_cookies, array('name'=>'testCookie'));
        $res = \Seraphp\Comm\Http\HttpFactory::create(
            'cookie', null, $this->_cookies
        );
        $this->assertType(
            PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $res
        );
        $this->assertThat(
            $res[0], $this->isInstanceOf('\Seraphp\Comm\Http\HttpCookie')
        );
    }

    function testGetValidHttpStatusCodes()
    {
        $this->assertEquals(
            'Continue', \Seraphp\Comm\Http\HTTPFactory::getHttpStatus(100)
        );
    }

    function testGetNotValidHttpStatusCodes()
    {
        $this->assertEquals(
            null, \Seraphp\Comm\Http\HTTPFactory::getHttpStatus(900)
        );
    }
}