<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
require_once 'PHPUnit/Framework.php';
require_once 'Comm/RequestFactory.class.php';
/**
 * Class documentation
 */
class RequestFactoryTest extends PHPUnit_Framework_TestCase
{

    private $_requests = array();
    private $_sockets = array();
    private $_sep = "\r\n";

    function setUp()
    {
        $this->_requests['get'] = 'GET /index.html HTTP/1.1'.
            $this->_sep.
            'Host:example.com'.
            $this->_sep.
            $this->_sep;
        $this->_requests['other'] = 'Árvíztűrő tükörfúrógép';
        $this->_requests['head'] = 'HEAD /index.html HTTP/1.1'.
            $this->_sep.
            'Host:example.com'.
            $this->_sep.
            $this->_sep;
        if(socket_create_pair(
            (strtoupper(substr(PHP_OS, 0, 3))=='WIN'?
            STREAM_PF_INET:STREAM_PF_UNIX
            ),
            STREAM_SOCK_STREAM,
            STREAM_IPPROTO_IP,
            $this->_sockets
        ) === false) {
            $this->fail(socket_strerror(socket_last_error()));
        }
    }

    function testGetProtocolOnGet()
    {
        $this->assertEquals(
            'http', RequestFactory::getProtocol($this->_requests['get'])
        );
    }

    function testGetProtocolOther()
    {
        $this->assertEquals(
            'other', RequestFactory::getProtocol($this->_requests['other'])
        );
    }

    function testGetProtocolOnHead()
    {
        $this->assertEquals(
            'http', RequestFactory::getProtocol($this->_requests['head'])
        );
    }

    function testCreateOnGet()
    {
        if (socket_write(
            $this->_sockets[0],
            $this->_requests['get'],
            strlen($this->_requests['get'])
        )===false) {
            $this->fail(
                socket_strerror(socket_last_error($this->_sockets[0]))
            );
        }
        socket_shutdown($this->_sockets[0], 2);
        $res = RequestFactory::create($this->_sockets[1]);
        $this->assertThat($res, $this->isInstanceOf('HttpRequest'));
    }

    function testCreateOnHead()
    {
        if (socket_write(
            $this->_sockets[0],
            $this->_requests['head'],
            strlen($this->_requests['head'])
        )===false) {
            $this->fail(socket_strerror(socket_last_error($this->_sockets[0])));
        }
        socket_shutdown($this->_sockets[0], 2);
        $res = RequestFactory::create($this->_sockets[1]);
        $this->assertThat($res, $this->isInstanceOf('HttpRequest'));
    }

    function tearDown()
    {
        foreach ($this->_sockets as $socket) {
            if (is_resource($socket)) {
                socket_shutdown($socket, 2);
            }
        }
    }
}