<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
require_once 'PHPUnit/Framework.php';
require_once 'Server/AppServer.class.php';
/**
 * Class documentation
 */
class AppServerTest extends PHPUnit_Framework_TestCase{
    protected $appID;
    protected $server;

    function setUp()
    {
        $this->appID = 'main';
    }

    function testAppServerInstatiation()
    {
        $this->server = new AppServer($this->appID);
        $this->assertEquals($this->server->getAppId(), $this->appID);
        $this->assertTrue($this->server->summon());
        $this->assertFileExists('/home/peter/workspace/phaser/.phaser'.$this->appID.'.pid');
        $this->assertEquals(5, $this->server->getMaxSpawns());
        $this->server->setMaxSpawns(10);
        $this->assertEquals(10, $this->server->getMaxSpawns());
    }
}
?>