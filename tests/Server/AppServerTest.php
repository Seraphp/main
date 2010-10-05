<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */

require_once 'Server/AppServer.class.php';
require_once 'Server/DefaultEngine.class.php';
/**
 * Class documentation
 */
class AppServerTest extends PHPUnit_Framework_TestCase
{
    private $_appID;
    private $_server = null;
    private $_conf = null;
    private $_confString;

    function setUp()
    {
        $this->_appID = 'main';
        $this->_confString = <<<XML
<servers>
    <server id="$this->_appID">
        <instance>
            <address>localhost</address>
            <port>1088</port>
        </instance>
    </server>
</servers>
XML;

    }

    function testAppServerInstatiation()
    {
        $this->_conf = new \Seraphp\Server\Config\Config($this->_confString);
        $this->_server = new \Seraphp\Server\AppServer($this->_conf->server);
        $this->_server->daemonize = false;
        $this->assertEquals($this->_appID, $this->_server->getAppId());
        $this->assertTrue(is_numeric($this->_server->summon()));
        //$this->assertFileExists(getcwd().'/.'.$this->_appID.'_srphp.pid');
        $this->assertEquals(5, $this->_server->getMaxSpawns());
        $this->_server->setMaxSpawns(10);
        $this->assertEquals(10, $this->_server->getMaxSpawns());
    }

    function tearDown()
    {
        $this->_server->expel();
        unset($this->_server);
    }
}