<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
require_once 'PHPUnit/Framework.php';
require_once 'Comm/JsonRpc/JsonRpcRequest.class.php';
/**
 * Class documentation
 */
class JsonRpcRequestTest extends PHPUnit_Framework_TestCase
{
    private $_names = array('main', 'mockery');

    function setUp()
    {
        foreach ($this->_names as $fifo) {
            $fileName = '/tmp/seraphp/'.$fifo.'I.tmp';
            if (file_exists($fileName)) {
                unlink($fileName);
            }
            $fileName = '/tmp/seraphp/'.$fifo.'O.tmp';
            if (file_exists($fileName)) {
                unlink($fileName);
            }
            if (is_dir('/tmp/seraphp')) {
                $curr = getcwd();
                chdir('/tmp');
                @rmdir('seraphp');
                chdir($curr);
            }
        }
    }

    function testConstructor()
    {
        $request = new JsonRpcRequest('call', null, 1);
        $this->assertEquals('call', $request->method);
        $this->assertEquals(1, $request->id);
        $this->assertEquals(null, $request->params);
    }

    function testConstructorNoMethod()
    {
        $this->setExpectedException('Exception', 'Method has to be defined!');
        $request = new JsonRpcRequest(null, null, 1);
    }

    function testToString()
    {
        $request = new JsonRpcRequest('call', null, 1);
        $this->assertEquals(
            '{"id":1,"method":"call","params":null}', $request->__toString()
        );
    }

    function tearDown()
    {
        $this->setUp();
    }
}