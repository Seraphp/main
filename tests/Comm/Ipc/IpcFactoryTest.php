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
        $this->assertEquals(getcwd().'/Comm/Ipc',IpcFactory::getPluginsDir());
        IpcFactory::setPluginsDir('./Comm');
        $this->assertEquals('./Comm',IpcFactory::getPluginsDir());
        $this->assertFalse(IpcFactory::setPluginsDir('./seraph'));
        $this->assertEquals('./Comm',IpcFactory::getPluginsDir());
    }

    function testValidPlugin()
    {
        IpcFactory::setPluginsDir('./Comm/Ipc');
        $this->assertThat(IpcFactory::get('unixsockets',0),$this->IsInstanceOf('IpcAdapter'));
    }

    function testNonexistingPlugin()
    {
        $this->setExpectedException('PluginException');
        IpcFactory::get('something',0);
    }
}
?>