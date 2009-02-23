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
        $this->conf->name = 'main';
        $this->conf->instance = array('address'=>'127.0.0.1', 'port'=>8080);
        $this->server = new AppServer($this->conf);
    }

    function testAppServerInstatiation()
    {
        $this->assertEquals('main',$this->server->getAppId());
        $this->assertTrue(is_numeric($this->server->summon()));
        $this->assertFileExists('/home/peter/workspace/seraphp/.seraphpmain.pid');
        $this->assertEquals(5, $this->server->getMaxSpawns());
        $this->server->setMaxSpawns(10);
        $this->assertEquals(10, $this->server->getMaxSpawns());
    }
}
?>