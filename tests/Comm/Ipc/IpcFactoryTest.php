<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */

require_once 'Comm/Ipc/IpcFactory.class.php';
/**
 * Class documentation
 */
class IpcFactoryTest extends PHPUnit_Framework_TestCase
{

    function testGetClassName()
    {
        $this->assertEquals(
            'Seraphp\Comm\Ipc\IpcUnixsockets',
            \Seraphp\Comm\Ipc\IpcFactory::getClassName('unixsockets')
        );
    }

    function testPluginsDir()
    {
        $this->assertEquals(
            getcwd().'/Comm/Ipc', \Seraphp\Comm\Ipc\IpcFactory::getPluginsDir()
        );
        \Seraphp\Comm\Ipc\IpcFactory::setPluginsDir('./Comm');
        $this->assertEquals(
            './Comm', \Seraphp\Comm\Ipc\IpcFactory::getPluginsDir()
        );
        $this->assertFalse(
            \Seraphp\Comm\Ipc\IpcFactory::setPluginsDir('./seraph')
        );
        $this->assertEquals(
            './Comm', \Seraphp\Comm\Ipc\IpcFactory::getPluginsDir()
        );
    }

    function testValidPlugin()
    {
        \Seraphp\Comm\Ipc\IpcFactory::setPluginsDir('./Comm/Ipc');
        $this->assertThat(
            \Seraphp\Comm\Ipc\IpcFactory::get('unixsockets', 0),
            $this->IsInstanceOf('\Seraphp\Comm\Ipc\IpcAdapter')
        );
    }

    function testNonexistingPlugin()
    {
        $this->setExpectedException('\Seraphp\Exceptions\PluginException');
        \Seraphp\Comm\Ipc\IpcFactory::get('something', 0);
    }
}