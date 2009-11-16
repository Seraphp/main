<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
require_once 'PHPUnit/Framework.php';
require_once 'Comm/Http/HttpRequest.class.php';
/**
 * Class documentation
 */
class HttpRequestTest extends PHPUnit_Framework_TestCase
{
    private $_requestString = array();
    private $_sockets = array();
    private $_sep = "\r\n";
    private $_params = array();
    private $_postMessage = '';

    function setUp()
    {
        $this->_params = array(
            'test'=>'example',
            'foo'=>'bar'
        );
        foreach ($this->_params as $key=>$value) {
            $this->_postMessage .= urlencode($key).'='.urlencode($value).'&';
        }
        $this->_postMessage = trim($this->_postMessage, "&");
        $this->_requestString['get'] = 'GET /index.html?test=1 HTTP/1.1'.
            $this->_sep.
            'Host:example.com'.
            $this->_sep.
            'Referer:example.com'.
            $this->_sep.
            'Cookie:seraphp=server'.
            $this->_sep.
            'Accept-Language:hu-hu,hu;q=0.8,en-us;q=0.5,en;q=0.3'.
            $this->_sep.
            $this->_sep;
        $this->_requestString['post'] = 'POST /index.html HTTP/1.1'.
            $this->_sep.
            'Host:example.com'.
            $this->_sep.
            $this->_sep.
            $this->_postMessage.
            $this->_sep;
        $this->_sockets = stream_socket_pair(
            (strtoupper(substr(PHP_OS, 0, 3))=='WIN'?
            STREAM_PF_INET:STREAM_PF_UNIX),
            STREAM_SOCK_STREAM,
            STREAM_IPPROTO_IP
        );
        if ($this->_sockets === false) {
            $this->fail(socket_strerror(socket_last_error()));
        }
    }

    function testConstructor()
    {
        $request = new HttpRequest();
        $this->assertFalse($request->isReceived);
    }

    function testConstructorWithGet()
    {
        if (fwrite(
            $this->_sockets[1],
            $this->_requestString['get'],
            strlen($this->_requestString['get'])
        ) === false) {
            $this->fail(socket_strerror(socket_last_error($this->_sockets[1])));
        }
        stream_socket_shutdown($this->_sockets[1], STREAM_SHUT_RDWR);
        $request = new HttpRequest($this->_sockets[0]);
        $this->assertTrue($request->isReceived);
        $this->assertEquals('1.1', $request->httpVersion);
        $this->assertEquals('GET', $request->method);
        $this->assertEquals('example.com', $request->httpHeaders['Host']);
        $this->assertEquals('example.com', $request->httpHeaders['Referer']);
        $this->assertContains(
            'hu-hu, hu', $request->httpHeaders['Accept-Language']
        );
        $this->assertContains(
            'q=0.8,en-us', $request->httpHeaders['Accept-Language']
        );
        $this->assertContains(
            'q=0.5,en', $request->httpHeaders['Accept-Language']
        );
        $this->assertContains(
            'q=0.3', $request->httpHeaders['Accept-Language']
        );
        $this->assertEquals('/index.html?test=1', $request->url);
        $this->assertEquals(1, $request->getParams['test']);
        $this->assertEquals(
            trim($this->_requestString['get']), $request->httpRawHeaders
        );
    }

    function testConstructorWithPost()
    {

        if (fwrite(
            $this->_sockets[1],
            $this->_requestString['post'],
            strlen($this->_requestString['post'])
        ) === false) {
            $this->fail(socket_strerror(socket_last_error($this->_sockets[1])));
        }
        stream_socket_shutdown($this->_sockets[1], STREAM_SHUT_RDWR);
        $request = new HttpRequest($this->_sockets[0]);
        $this->assertTrue($request->isReceived);
        $this->assertEquals('1.1', $request->httpVersion);
        $this->assertEquals('POST', $request->method);
        $this->assertEquals('example.com', $request->httpHeaders['Host']);
        $this->assertEquals('/index.html', $request->url);
        $this->assertEquals(
            "POST /index.html HTTP/1.1\r\nHost:example.com",
            $request->httpRawHeaders
        );
        $this->assertEquals(
            urlencode($this->_postMessage), urlencode($request->message)
        );
        $this->assertEquals($this->_params, $request->postParams);
    }

    function testResponse()
    {
        if (fwrite(
            $this->_sockets[1],
            $this->_requestString['get'],
            strlen($this->_requestString['get'])
        ) === false) {
            $this->fail(socket_strerror(socket_last_error($this->_sockets[1])));
        }
        stream_socket_shutdown($this->_sockets[1], STREAM_SHUT_RDWR);
        $request = new HttpRequest($this->_sockets[0]);
        $this->assertTrue($request->isReceived);
        $this->assertThat(
            $request->respond('Testing'), $this->isInstanceOf('HttpResponse')
        );
    }

    function testListener()
    {
        require_once 'ObservableListener.interface.php';
        $obs = $this->getMock('Listener');
        $obs->expects($this->once())->method('getName')->will(
            $this->returnValue('mockListener')
        );
        $obs->expects($this->any())->method('update');

        if (fwrite(
            $this->_sockets[1],
            $this->_requestString['get'],
            strlen($this->_requestString['get'])
        ) === false) {
            $this->fail(socket_strerror(socket_last_error($this->_sockets[1])));
        }
        stream_socket_shutdown($this->_sockets[1], STREAM_SHUT_RDWR);
        $request = new HttpRequest($this->_sockets[0]);
        $this->assertEquals('mockListener', $request->attach($obs));
        $this->assertType('int', $request->getState());
        $this->assertTrue($request->detach('mockListener'));
    }

    /**
     * @dataProvider versions
     */
    function testSendingRequest($version)
    {
        $req = new HttpRequest();
        $this->assertFalse($req->isReceived);
        $req->url = 'http://localhost';
        $req->getParams = array('test'=>1);
        $req->httpVersion = $version;
        $req->cookies = HttpFactory::create(
            'cookie',
            null,
            array(
                'name'=>'test',
                'value'=>2,
                'domain'=>'localhost'
            )
        );
        $resp = $req->send();
        $this->assertThat($resp, $this->isInstanceOf('HttpResponse'));
        $this->assertEquals('text/html', $resp->contentType);
        $this->assertEquals('1.1', $resp->httpVersion);
        $this->assertEquals(200, $resp->statusCode);
        $this->assertEquals('OK', $resp->statusLine);
        $this->assertArrayHasKey('Date', $resp->headers);
        $this->assertArrayHasKey('ETag', $resp->headers);
        $this->assertArrayHasKey('Server', $resp->headers);
        $this->assertArrayHasKey('Content-Length', $resp->headers);
        $this->assertEquals(
            '<html><body><h1>It works!</h1></body></html>', $resp->messageBody
        );
    }

    function versions()
    {
        return array(array('1.x'), array('1.0'), array('1.1'));
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