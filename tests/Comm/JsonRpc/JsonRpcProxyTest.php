<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
require_once 'PHPUnit/Framework.php';
require_once 'Comm/JsonRpc/JsonRpcProxy.class.php';
require_once 'Server/Server.class.php';
/**
 * Class documentation
 */
class JsonRpcProxyTest extends PHPUnit_Framework_TestCase
{
    private $_names = array('mockery');
    private $_mockClass= null;

    private function cleanUp()
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

    function setUp()
    {
         $this->cleanUp();
         $this->_mockClass = $this->getMock(
            'runningClass', array('getStatus', 'getRole')
         );
    }

    function testConstructorWithClientArray()
    {
        $proxy = new JsonRpcProxy(
            $this->_names[0], array($this->_mockClass, getmypid())
        );
        $proxy->init('server');
        $this->assertFileExists('/tmp/seraphp/'.$this->_names[0].'I.tmp');
        $this->assertFileExists('/tmp/seraphp/'.$this->_names[0].'O.tmp');
    }

    function testCallAtClient()
    {
        $clientProxy = new JsonRpcProxy(
            $this->_names[0], array($this->_mockClass, getmypid())
        );
        $clientProxy->init('client');
        $pipe = fopen('/tmp/seraphp/'.$this->_names[0].'O.tmp', 'r+');
        stream_set_blocking($pipe, false);
        fwrite($pipe, (string) new JsonRpcResponse('running', null, 0));
        pcntl_signal(SIGUSR1, SIG_IGN);
        $status = $clientProxy->getStatus();
        $this->assertEquals('running', $status);
        pcntl_signal(SIGUSR1, SIG_DFL);
    }

    function testCallAtServer()
    {
        $serverProxy = new JsonRpcProxy(
            $this->_names[0], new RunningClass
        );
        $serverProxy->init('server');
        $pipeO = fopen('/tmp/seraphp/'.$this->_names[0].'O.tmp', 'r+');
        stream_set_blocking($pipeO, false);
        $pipeI = fopen('/tmp/seraphp/'.$this->_names[0].'I.tmp', 'w+');
        stream_set_blocking($pipeI, false);
        fwrite($pipeI, (string) new JsonRpcRequest('getStatus', null, 0));
        $serverProxy->listen();
        $read = array($pipeO);
        $write = array();
        $exc = array();
        $jsonResponse = '';
        if (stream_select($read, $write, $exc, 5) > 0) {
            $jsonResponse = trim(fgets($pipeO));
        }
        $this->assertEquals(
            (string) new JsonRpcResponse('running', null, 0), $jsonResponse
        );
    }

    function tearDown()
    {
        $this->cleanUp();
    }
}

class RunningClass
{
    public function getStatus()
    {
        return 'running';
    }

    public function getRole()
    {
        return 'parent';
    }
}
