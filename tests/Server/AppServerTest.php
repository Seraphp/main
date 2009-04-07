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
        $this->conf = new Config;
        $this->appID = 'main';
        $this->conf->name = $this->appID;
        $this->conf->instance = array('address'=>'localhost', 'port'=>1088);
        $this->server = new AppServer($this->conf);
    }

    function testAppServerInstatiation()
    {
        $this->assertEquals('main',$this->server->getAppId());
        $this->assertTrue(is_numeric($this->server->summon()));
        $this->assertFileExists(getcwd().'/.'.$this->appID.'_srphp.pid');
        $this->assertEquals(5, $this->server->getMaxSpawns());
        $this->server->setMaxSpawns(10);
        $this->assertEquals(10, $this->server->getMaxSpawns());
    }
}