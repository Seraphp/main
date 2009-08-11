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
        $this->conf = new Config('<test><foo/><bar/></test>');
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

    function testXSearch()
    {
        $result = $this->conf->xsearch('/test');
        $this->assertType('array',$result);
        $this->assertType('object',$result[0]);
        $this->assertObjectHasAttribute('foo',$result[0]);
        $this->assertObjectHasAttribute('bar',$result[0]);
    }

    function testXSearchFromNode()
    {
        $result = $this->conf->xsearch('//foo', $this->conf->test);
        $this->assertType('array',$result);
        $this->assertType('object',$result[0]);
    }

    function tearDown()
    {
        unset($this->conf);
    }
}