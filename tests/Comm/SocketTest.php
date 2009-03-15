<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id: IpcUnixsocketsTest.php 298 2008-12-31 20:08:49Z peter $
 * @filesource
 */
require_once 'PHPUnit/Framework.php';
require_once 'Comm/Socket.class.php';
/**
 * Class documentation
 */
class SocketTest extends PHPUnit_Framework_TestCase{

    function setUp()
    {
        $this->msg = 'Árvíztűrő tükörfúrógép';
        $this->soc = new Socket('unix','/tmp/peter/socTest.tmp');
    }

    function testSupportedTransports()
    {
        $this->assertContains('unix', Socket::supportedTransports());
        $this->assertContains('tcp', Socket::supportedTransports());
        $this->assertContains('udp', Socket::supportedTransports());
    }

    function testSettingValidUnixAddress()
    {
        $this->assertEquals('/tmp/peter/socTest.tmp', $this->soc->getAddress());
    }

    function testSettingEmptyAddress()
    {
        $this->setExpectedException('SocketException');
        $this->soc->setAddress('');
    }

    function testSettingIPv4Address()
    {
        $this->soc->setAddress('127.0.0.1');
    }

    function testSettingValidHostAddress()
    {
        $this->soc->setAddress('localhost');
    }

    function testSettingInvalidHostAddress()
    {
        $this->soc->setAddress('example.com');
    }

    function testGetSetPort()
    {
        $this->assertEquals(0, $this->soc->getPort());
        $this->assertEquals(80, $this->soc->setPort(80));
        $this->assertEquals(80, $this->soc->setPort(65616));
    }

    function testGetSetBlocking()
    {
        $this->assertTrue($this->soc->isBlocking());
        $this->assertTrue($this->soc->setBlocking(false));
        $this->assertFalse($this->soc->isBlocking());
    }

    function testGetSetPersistent()
    {
        $this->assertFalse($this->soc->isPersistent());
        $this->assertTrue($this->soc->setPersistent(true));
        $this->assertTrue($this->soc->isPersistent());
    }

    function testGetStatusWhenNotConnected()
    {
        $this->setExpectedException('SocketException');
        $this->assertArrayHasKey('stream_type', $this->soc->getStatus());
    }

    function testGetSetTransport()
    {
        $this->assertEquals('unix', $this->soc->getTransp());
        $this->assertTrue($this->soc->setTransp('tcp'));
        $this->assertEquals('tcp', $this->soc->getTransp());
    }

    function testSetInvalidTransport()
    {
        $this->setExpectedException('SocketException');
        $this->soc->setTransp('bar');
    }

    function testGetSetTimeout()
    {
        $this->assertEquals(0, $this->soc->getTimeout());
        $this->assertTrue($this->soc->setTimeout(60));
        $this->assertEquals(60000000, $this->soc->getTimeout());
    }

    function testSetWriteBufferNotConnected()
    {
        $this->setExpectedException('SocketException');
        $this->assertTrue($this->soc->setWriteBuffer(10));
    }

    function testSetOptions()
    {
        $this->soc->setOptions( array('foo'=>'bar') );
        $this->assertEquals( array('foo'=>'bar'), $this->soc->getOptions() );
    }

    function testReadsNotConnected()
    {
        $this->setExpectedException('SocketException');
        $this->soc->read(10);
        $this->setExpectedException('SocketException');
        $this->soc->readByte();
        $this->setExpectedException('SocketException');
        $this->soc->readAll();
        $this->setExpectedException('SocketException');
        $this->soc->readIPAddress();
        $this->setExpectedException('SocketException');
        $this->soc->readInt();
        $this->setExpectedException('SocketException');
        $this->soc->readLine();
        $this->setExpectedException('SocketException');
        $this->soc->readString();
        $this->setExpectedException('SocketException');
        $this->soc->readWord();
    }

    function testReadsConnected()
    {
        $this->getTcpConnected();
        $this->soc->writeLine('GET / HTTP/1.0');
        $this->soc->writeLine('');
        $this->assertEquals(10,strlen($this->soc->read(10)));
        $this->assertLessThan(3,strlen($this->soc->readByte()));
        $this->assertLessThan(16,strlen($this->soc->readIPAddress()));
        $this->assertEquals(10,strlen($this->soc->readInt()));
        $this->assertEquals(5,strlen($this->soc->readWord()));
    }

    function testReadLineConnected()
    {
        $this->getTcpConnected();
        $this->soc->write('HEAD / HTTP/1.0');
        $this->soc->writeLine('');
        $this->soc->writeLine('');
        $this->assertEquals(35,strlen($this->soc->readLine()));
    }

    function testWrite()
    {
        $this->setExpectedException('SocketException');
        $this->soc->writeLine($this->msg);
        $this->setExpectedException('SocketException');
        $this->soc->write($this->msg);
        $this->getTcpConnected();
        $this->assertTrue($this->soc->writeLine($this->msg));
        $this->assertTrue($this->soc->write($this->msg));
    }


    function testTcpConnect()
    {
        $this->soc->setTransp('tcp');
        $this->soc->setAddress('localhost');
        $this->soc->setPort(80);
        $this->soc->connect();
        $this->soc->disconnect();
    }

    function testTcpReconnect()
    {
        $this->getTcpConnected();
        $this->soc->connect();
        $this->soc->disconnect();
    }

    function testTcpRead()
    {
        $this->getTcpConnected();
        $this->soc->writeLine('GET / HTTP/1.0');
        $this->soc->writeLine('');
        $response = $this->soc->read(100);
        $this->assertEquals(100,strlen($response));
        $response .= $this->soc->gets(100);
        $this->assertEquals(199,strlen($response));
        $response .= $this->soc->readAll();
        $this->assertEquals(942,strlen($response));
        $this->soc->disconnect();
    }

    function testGetStatusWhenConnected()
    {
        $this->getTcpConnected();
        $this->assertArrayHasKey('stream_type', $this->soc->getStatus());
    }

    function testSetWriteBufferConnected()
    {
        $this->getTcpConnected();
        $this->setExpectedException( 'SocketException' );
        $this->assertTrue( $this->soc->setWriteBuffer( 0 ) );
    }

    function testDestructor()
    {
        $this->getTcpConnected();
        $con = $this->soc;
        $this->soc = null;
    }

    function getTcpConnected()
    {
        $this->soc->setTransp('tcp');
        $this->soc->setAddress('127.0.0.1');
        $this->soc->setPort(80);
        $this->soc->connect();
    }

    function tearDown()
    {
        unset($this->soc);
    }
}
?>