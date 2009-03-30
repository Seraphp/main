<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
require_once 'PHPUnit/Framework.php';
require_once 'Comm/Ipc/IpcUnixsockets.class.php';
/**
 * Class documentation
 */
class IpcUnixsocketsTest extends PHPUnit_Framework_TestCase{

    function setUp()
    {
        $this->msg = 'Árvíztűrő tükörfúrógép';
        $this->ipc = new IpcUnixsockets();
        $this->ipc->init(1234,'child');
    }

    function testInit()
    {
        $this->assertEquals('child',$this->ipc->init(1234,'child'));
    }

    function testRoleSettings()
    {
        $this->assertEquals('child',$this->ipc->getRole());
        $this->assertEquals('parent',$this->ipc->setRole('parent'));
        $this->assertEquals('parent',$this->ipc->setRole('test'));
    }

    function testWrite()
    {
        $this->assertEquals(strlen($this->msg)+1,$this->ipc->write(1234, $this->msg));
    }

    function testRead()
    {
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
        $this->assertEquals($this->msg,$this->ipc->read());
    }

    function testClose()
    {
        $this->ipc->close();
    }

    function testDestructor()
    {
        unset($this->ipc);
    }
}