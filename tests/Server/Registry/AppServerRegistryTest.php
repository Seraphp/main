<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
require_once 'PHPUnit/Framework.php';
require_once 'Server/Registry/AppServerRegistry.class.php';
require_once 'Server/AppServer.class.php';
require_once 'Server/DefaultEngine.class.php';
/**
 * Class documentation
 */
class AppServerRegistryTest extends PHPUnit_Framework_TestCase{

    private $reg = null;
    private $conf = null;
    private static $port = 8081;

    function setUp()
    {
        $this->reg = AppServerRegistry::getInstance();
        $confString = <<<XML
<?xml version='1.0' standalone='yes'?>
    <servers>
        <server id="main">
            <instance>
                <port>self::$port</port>
            </instance>
        </server>
    </servers>
XML;

        $this->conf = new Config($confString);
        self::$port++;
        $this->mockServer = new AppServer($this->conf);
    }

    function testRegistryIsSingleton()
    {
        $this->assertThat($this->reg, $this->IsInstanceOf('AppServerRegistry'));
        $this->assertSame($this->reg, AppServerRegistry::getInstance());
    }

    function testGetNonRunningServerStatus()
    {
        $this->assertNull($this->reg->getAppStatus('something'));
    }

    function testGetNonRunningServerInstance()
    {
        $this->assertNull($this->reg->getAppInstance('something'));
    }

    function testAddValidApp()
    {
        $this->assertTrue($this->reg->addApp('mockery', $this->mockServer));
        $this->assertEquals($this->reg->getAppStatus('mockery'), null);
        $this->assertThat($this->reg->getAppInstance('mockery'),
            $this->IsInstanceOf('JsonRpcProxy'));
        $this->assertThat($this->reg->removeApp('mockery'),
            $this->IsInstanceOf('JsonRpcProxy'));
        $this->setExpectedException('RegistryException');
        $this->reg->removeApp('mockery');
    }

    function testAddSameApp()
    {
        $this->assertTrue($this->reg->addApp('mockery', $this->mockServer));
        $this->setExpectedException('RegistryException');
        $this->reg->addApp('mockery',$this->mockServer);
        $this->reg->removeApp('mockery');
    }

    function tearDown()
    {
        if (file_exists('./.srpdAppMan')) {
            //unlink('./.srpdAppMan');
        }
    }

}