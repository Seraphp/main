<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
require_once 'PHPUnit/Framework.php';
require_once 'Server/Config/Config.class.php';
/**
 * Class documentation
 */
class ConfigTest extends PHPUnit_Framework_TestCase{

    private $reg = null;

    function setUp()
    {
        $this->conf = new Config('<test />');
    }

    function testEmptyConfigKeyIsInvalid()
    {
        $this->assertFalse(isset($this->conf->somekey));
    }

    function testEmptyConfigKeyReturnsNull()
    {
        $this->assertNull($this->conf->somekey);
    }

    function testConfigyKeyValid()
    {
        $this->conf->somekey->foo->bar='somevalue';
        $this->assertTrue(isset($this->conf->somekey));
    }

    function testIsChanged()
    {
        $this->conf->somekey->foo->bar='somevalue';
        $this->assertTrue($this->conf->isChanged());
    }

    function testClearState()
    {
        $this->conf->somekey->foo->bar='somevalue';
        $this->conf->clearState();
        $this->assertFalse($this->conf->isChanged());
    }

    function tearDown()
    {
        unset($this->conf);
    }
}