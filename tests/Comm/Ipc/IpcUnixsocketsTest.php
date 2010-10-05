<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */

require_once 'Comm/Ipc/IpcUnixsockets.class.php';
/**
 * Class documentation
 */
class IpcUnixsocketsTest extends PHPUnit_Framework_TestCase
{
    protected $_ipc;
    protected $_msg;

    function setUp()
    {
        $this->_msg = 'Árvíztűrő tükörfúrógép';
        $this->_ipc = new \Seraphp\Comm\Ipc\IpcUnixsockets();
        $this->_ipc->init(1234, 'child');
    }

    function testInit()
    {
        $this->assertEquals('child', $this->_ipc->init(1234, 'child'));
    }

    function testRoleSettings()
    {
        $this->assertEquals('child', $this->_ipc->getRole());
        $this->assertEquals('parent', $this->_ipc->setRole('parent'));
        $this->assertEquals('parent', $this->_ipc->setRole('test'));
    }

    function testWrite()
    {
        $this->assertEquals(
            strlen($this->_msg)+1, $this->_ipc->write(1234, $this->_msg)
        );
    }


    function testClose()
    {
        $this->_ipc->close();
    }

    function testDestructor()
    {
        unset($this->_ipc);
    }
}