<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
require_once 'PHPUnit/Framework.php';
require_once 'Comm/Http/HttpResponse.class.php';
/**
 * Class documentation
 */
class HttpResponseTest extends PHPUnit_Framework_TestCase
{
    private $_body = '';
    private $_hash = '';
    private $_sockets = null;
    private $_http = '';
    private $_httpLastModified = '';
    private $_bodyFile = '';

    function setUp()
    {
        $this->_bodyFile =realpath(getcwd().'/tests/test.txt');
        $this->_body = file_get_contents($this->_bodyFile);
        $this->_sockets = stream_socket_pair(
            (strtoupper(substr(PHP_OS, 0, 3))=='WIN'?
            STREAM_PF_INET:STREAM_PF_UNIX),
            STREAM_SOCK_STREAM,
            STREAM_IPPROTO_IP
        );
        if ($this->_sockets === false) {
            $this->fail(socket_strerror(socket_last_error()));
        }
        $this->_hash = md5($this->_body);
        $length = strlen($this->_body);
        $date = date(DATE_RFC1123);
        $this->_http = <<<MSG
HTTP/1.x 200 OK
Server:Seraphp 0.1
Date:$date
Connection:Closed
Content-Length:$length
Etag:$this->_hash
Content-Type:text/plain

$this->_body

MSG;
    $mtime = date(DATE_RFC1123, filemtime($this->_bodyFile));
    $this->_httpLastModified = <<<MSG
HTTP/1.x 200 OK
Server:Seraphp 0.1
Date:$date
Connection:Closed
Content-Length:$length
Last-Modified:$mtime
Etag:$this->_hash
Content-Type:text/plain

$this->_body

MSG;
        $this->_http = str_replace("\n", "\r\n", $this->_http);
        $this->_httpLastModified =
            str_replace("\n", "\r\n", $this->_httpLastModified);
    }

    function testConstructor()
    {
        $resp = new HttpResponse();
        $this->assertFalse($resp->toBeSend);
        $resp = new HttpResponse($this->_sockets[0]);
        $this->assertTrue($resp->toBeSend);
        $this->assertEquals($resp->contentType, 'text/plain');
        $this->assertEquals($resp->statusCode, 200);
        $this->assertEquals($resp->httpVersion, '1.x');
    }

    function testSend()
    {
        $resp = new HttpResponse($this->_sockets[0]);
        $resp->messageBody = $this->_body;
        $resp->send();
        $result = fread($this->_sockets[1], 2048);
        $this->assertEquals($this->_http, $result);
    }

    function testSendWithStreamBody()
    {
        $resp = new HttpResponse($this->_sockets[0]);
        $bodyRs = fopen($this->_bodyFile, "r");
        $resp->messageBody = $bodyRs;
        $resp->send();
        $result = fread($this->_sockets[1], 2048);
        $this->assertEquals($this->_httpLastModified, $result);
    }

    function tearDown()
    {
        foreach ($this->_sockets as $socket) {
            if (is_resource($socket)) {
                stream_socket_shutdown($socket, STREAM_SHUT_RDWR);
            }
        }
    }
}