<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
require_once 'PHPUnit/Framework.php';
require_once 'Server/AppServer.class.php';
require_once 'Server/DefaultEngine.class.php';
/**
 * Class documentation
 */
class AppServerTest extends PHPUnit_Framework_TestCase{
    protected $appID;
    protected $server;

    function setUp()
    {
        $this->appID = 'main';
        $confString = <<<XML
<servers>
    <server id="$this->appID">
        <instance>
            <address>localhost</address>
            <port>1088</port>
        </instance>
    </server>
</servers>
XML;
        $this->conf = new Config($confString);
        $this->server = new AppServer($this->conf->server);
    }

    function testAppServerInstatiation()
    {
        $this->assertEquals($this->appID, $this->server->getAppId());
        $this->assertTrue(is_numeric($this->server->summon()));
        $this->assertFileExists(getcwd().'/.'.$this->appID.'_srphp.pid');
        $this->assertEquals(5, $this->server->getMaxSpawns());
        $this->server->setMaxSpawns(10);
        $this->assertEquals(10, $this->server->getMaxSpawns());
    }
}