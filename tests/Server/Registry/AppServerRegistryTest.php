<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */

require_once 'Server/Registry/AppServerRegistry.class.php';
require_once 'Server/AppServer.class.php';
require_once 'Server/DefaultEngine.class.php';
/**
 * Class documentation
 */
class AppServerRegistryTest extends PHPUnit_Framework_TestCase
{

    private $_reg = null;
    private $_conf = null;
    private static $_port = 8081;
    private $_mockServer = null;

    function setUp()
    {
        if (file_exists('./.srpdAppMan')) {
            unlink('./.srpdAppMan');
        }
        $this->_reg = \Seraphp\Server\Registry\AppServerRegistry::getInstance();
        $confString = <<<XML
<?xml version='1.0' standalone='yes'?>
    <servers>
        <server id="main">
            <instance>
                <port />
            </instance>
        </server>
    </servers>
XML;

        $this->_conf = new \Seraphp\Server\Config\Config($confString);
        $this->_conf->server->instance->port = self::$_port++;
        $this->_mockServer = new \Seraphp\Server\AppServer(
            $this->_conf->server
        );
    }

    function testRegistryIsSingleton()
    {
        $this->assertThat(
            $this->_reg,
            $this->IsInstanceOf('Seraphp\Server\Registry\AppServerRegistry')
        );
        $this->assertSame(
            $this->_reg,
            \Seraphp\Server\Registry\AppServerRegistry::getInstance()
        );
    }

    function testGetNonRunningServerStatus()
    {
        $this->assertEquals(
            $this->_reg->getAppStatus('something'), 'not running'
        );
    }

    function testGetNonRunningServerInstance()
    {
        $this->assertNull($this->_reg->getAppInstance('something'));
    }

    function testAddValidApp()
    {
        $this->assertTrue($this->_reg->addApp('mockery', $this->_mockServer));
        $this->assertEquals(
            $this->_reg->getAppStatus('mockery'), 'not running'
        );
        $this->_reg->removeApp('mockery');
    }

/*    function testGetRpcInstance()
    {
        $this->assertTrue($this->_reg->addApp('mockery', $this->_mockServer));
        $this->assertEquals(
            $this->_reg->getAppInstance('mockery'),
            'AppServer'
        );
        $this->_reg->removeApp('mockery');
    }*/

/*    function testGetRpcByRemove()
    {
        $this->assertTrue($this->_reg->addApp('mockery', $this->_mockServer));
        $this->assertEquals(
            $this->_reg->removeApp('mockery'), 'AppServer'
        );
    }*/

/*    function testRemoveNonExistentIsExcpetion()
    {
        $this->setExpectedException('RegistryException');
        $this->_reg->removeApp('mockery');
    }*/

/*    function testDoubleRemovalException()
    {
        $this->assertTrue($this->_reg->addApp('mockery', $this->_mockServer));
        $this->_reg->removeApp('mockery');
        $this->setExpectedException('RegistryException');
        $this->_reg->removeApp('mockery');
    }*/

/*    function testAddSameApp()
    {
        $this->assertTrue($this->_reg->addApp('mockery', $this->_mockServer));
        $this->setExpectedException('RegistryException');
        $this->_reg->addApp('mockery', $this->_mockServer);
    }*/

    function tearDown()
    {
        try {
            $this->_reg->removeApp('mockery');
        } catch(\Seraphp\Exceptions\RegistryException $e) {
        }
        unset($this->_mockServer);
        unset($this->_conf);
        unset($this->_reg);
    }
}