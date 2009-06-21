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

    function testConfigKeyValid()
    {
        $this->conf->somekey->foo->bar='somevalue';
        $this->assertTrue(isset($this->conf->somekey));
    }

    function tearDown()
    {
        unset($this->conf);
    }
}