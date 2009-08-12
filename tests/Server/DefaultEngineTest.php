<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
require_once 'PHPUnit/Framework.php';
require_once 'Server/DefaultEngine.class.php';
require_once 'Server/Config/Config.class.php';
require_once 'Comm/Http/HttpRequest.class.php';
require_once 'Comm/Http/HttpResponse.class.php';
/**
 * Class documentation
 */
class DefaultEngineTest extends PHPUnit_Framework_TestCase{

    private $request = null;
    private $response = null;
    private $_sep = "\r\n";
    private $config;

    function setUp()
    {
        $this->request = $this->getMock('HttpRequest', array('respond'));
        $this->response = $this->getMock('HttpResponse', array('send'));
        $this->request->expects($this->once())
         ->method('respond')
         ->will($this->returnValue($this->response));
        $this->config = new Config('<foo/>');
    }

    function testProcess()
    {
        $engine = new DefaultEngine($this->config);
        $this->assertEquals(0, $engine->process($this->request));
    }

    function tearDown()
    {
        unset($this->request);
        unset($this->response);
        unset($this->config);
    }

}