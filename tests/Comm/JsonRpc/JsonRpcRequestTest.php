<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id: HttpRequestTest.php 566 2009-08-09 19:35:25Z peter $
 * @filesource
 */
require_once 'PHPUnit/Framework.php';
require_once 'Comm/Http/HttpRequest.class.php';
/**
 * Class documentation
 */
class JsonRpcRequestTest extends PHPUnit_Framework_TestCase{


    function testConstructor()
    {
        $request = new JsonRpcRequest('call', null, 1);
        $this->assertEquals('call',$request->method);
        $this->assertEquals(1,$request->id);
        $this->assertEquals(null,$request->params);
    }

    function testConstructorNoMethod()
    {
        $this->setExpectedException('Exception','Method has to be defined!');
        $request = new JsonRpcRequest(null, null, 1);
    }

    function testToString()
    {
        $request = new JsonRpcRequest('call', null, 1);
        $this->assertEquals('{"id":1,"method":"call","params":null}',$request->__toString());
    }
}