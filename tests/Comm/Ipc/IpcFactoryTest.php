<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
require_once 'PHPUnit/Framework.php';
require_once 'Comm/Ipc/IpcFactory.class.php';
/**
 * Class documentation
 */
class IpcFactoryTest extends PHPUnit_Framework_TestCase{

    function testGetClassName()
    {
        $this->assertEquals('IpcUnixsockets',IpcFactory::getClassName('unixsockets'));
    }

    function testPluginsDir()
    {
        $this->assertEquals('/home/peter/workspace/seraphp/Comm/Ipc',IpcFactory::getPluginsDir());
        IpcFactory::setPluginsDir('/home/peter/workspace/seraphp/Comm');
        $this->assertEquals('/home/peter/workspace/seraphp/Comm',IpcFactory::getPluginsDir());
        $this->assertFalse(IpcFactory::setPluginsDir('/home/peter/workspace/seraph'));
        $this->assertEquals('/home/peter/workspace/seraphp/Comm',IpcFactory::getPluginsDir());
    }

    function testValidPlugin()
    {
        IpcFactory::setPluginsDir('/home/peter/workspace/seraphp/Comm/Ipc');
        $this->assertThat(IpcFactory::get('unixsockets',0),$this->IsInstanceOf('IpcAdapter'));
    }

    function testNonexistingPlugin()
    {
        $this->setExpectedException('PluginException');
        IpcFactory::get('something',0);
    }
}
?>