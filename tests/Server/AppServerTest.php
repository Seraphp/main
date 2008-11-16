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
        $this->appID = 'phaserMain';
    }
    
    function testAppServerInstatiation()
    {
        $this->server = new AppServer($this->appID);
        $this->assertTrue($this->server->summon());
        $this->assertFileExists('/home/peter/workspace/phaser/.'.$this->appID.'.pid');
    }
    
    function tearDown()
    {
        $this->server->expell();
    }

}
?>