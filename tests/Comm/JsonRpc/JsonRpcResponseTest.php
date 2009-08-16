<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id: HttpRequestTest.php 566 2009-08-09 19:35:25Z peter $
 * @filesource
 */
require_once 'PHPUnit/Framework.php';
require_once 'Comm/JsonRpc/JsonRpcResponse.class.php';
/**
 * Class documentation
 */
class JsonRpcResponseTest extends PHPUnit_Framework_TestCase{

    private $names = array('main', 'mockery');

    function setUp()
    {
        foreach ($this->names as $fifo) {
            $fileName = '/tmp/seraphp/'.$fifo.'I.tmp';
            if (file_exists($fileName)) {
                unlink($fileName);
            }
            $fileName = '/tmp/seraphp/'.$fifo.'O.tmp';
            if (file_exists($fileName)) {
                unlink($fileName);
            }
            if(is_dir('/tmp/seraphp')){
                $curr = getcwd();
                chdir('/tmp');
                @rmdir('seraphp');
                chdir($curr);
            }
        }
    }

    function testConstructor()
    {
        $response = new JsonRpcResponse('result', null, 1);
        $this->assertEquals('result',$response->result);
        $this->assertEquals(1,$response->id);
        $this->assertEquals(null,$response->error);
    }

    function testConstructorNoResult()
    {
        $this->setExpectedException('Exception',
            'Either a result or an error should be set!');
        $response = new JsonRpcResponse(null, null, 1);

    }

    function testToString()
    {
        $response = new JsonRpcResponse('result', null, 1);
        $this->assertEquals('{"result":"result","error":null,"id":1}',$response->__toString());
    }

    function tearDown()
    {
        $this->setUp();
    }
}