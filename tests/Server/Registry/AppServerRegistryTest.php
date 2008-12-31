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
/**
 * Class documentation
 */
class AppServerRegistryTest extends PHPUnit_Framework_TestCase{

    private $reg = null;

    function setUp()
    {
        $this->reg = AppServerRegistry::getInstance();
        $this->mockServer = $this->getMock('AppServer', array('getStatus'),array('mockery'));
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
        $this->mockServer->expects($this->once())->method('getStatus');
        $this->assertTrue($this->reg->addApp('mockery',$this->mockServer));
        $this->assertEquals($this->reg->getAppStatus('mockery'),null);
        $this->assertSame($this->reg->getAppInstance('mockery'),$this->mockServer);
        $this->assertSame($this->mockServer, $this->reg->removeApp('mockery'));
        $this->setExpectedException('RegistryException');
        $this->reg->removeApp('mockery');
    }

    function testAddSameApp()
    {
        $this->assertTrue($this->reg->addApp('mockery',$this->mockServer));
        $this->setExpectedException('RegistryException');
        $this->reg->addApp('mockery',$this->mockServer);
        $this->reg->removeApp('mockery');
    }

}
?>