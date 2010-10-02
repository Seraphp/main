<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
require_once 'Server/DefaultEngine.class.php';
require_once 'Server/Config/Config.class.php';
require_once 'Comm/Http/HttpRequest.class.php';
require_once 'Comm/Http/HttpResponse.class.php';
/**
 * Class documentation
 */
class DefaultEngineTest extends PHPUnit_Framework_TestCase
{

    private $_request = null;
    private $_response = null;
    private $_sep = "\r\n";
    private $_config;

    function setUp()
    {
        $this->_request = $this->getMock('HttpRequest', array('respond'));
        $this->_response = $this->getMock('HttpResponse', array('send'));
        $this->_request->expects($this->once())
         ->method('respond')
         ->will($this->returnValue($this->_response));
        $this->_config = new Config('<foo/>');
    }

    function testProcess()
    {
        $engine = new DefaultEngine($this->_config);
        $this->assertEquals(0, $engine->process($this->_request));
    }

    function tearDown()
    {
        unset($this->_request);
        unset($this->_response);
        unset($this->config);
    }

}