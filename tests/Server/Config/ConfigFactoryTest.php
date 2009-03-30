<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
require_once 'PHPUnit/Framework.php';
require_once 'Server/Config/ConfigFactory.class.php';
/**
 * Class documentation
 */
class ConfigFactoryTest extends PHPUnit_Framework_TestCase{

    function setUp()
    {
        $this->cf = ConfigFactory::getInstance();
    }

    function testFactoryIsSingleton()
    {
        $this->assertThat($this->cf, $this->IsInstanceOf('ConfigFactory'));
        $this->assertSame($this->cf, ConfigFactory::getInstance());
    }

    function testCloningDisabled()
    {
        $this->setExpectedException('Exception');
        $newCf= clone $this->cf;
    }

    function testGetMainConf()
    {
        $this->cf->setXmlSrc(dirname(__FILE__).'/seraphp_test_config.xml');
        $conf = $this->cf->getConf('main');
        $this->assertThat($conf,$this->IsInstanceOf('Config'));
        $this->assertEquals('/home/peter/workspace/seraphp',$conf->pidpath);
        $this->assertEquals(5,$conf->instance['maxChildren']);
        $this->assertEquals('127.0.0.1',$conf->instance['address']);
        $this->assertEquals(8123,$conf->instance['port']);
        $this->assertType('array',$conf->urimap);
        $this->assertType('array',$conf->includes);
    }

    function testGetSessionConf()
    {
        $this->cf->setXmlSrc(dirname(__FILE__).'/seraphp_test_config.xml');
        $conf = $this->cf->getConf('session');
        $this->assertThat($conf,$this->IsInstanceOf('Config'));
        $this->assertEquals('/home/peter/workspace/seraphp',$conf->pidpath);
        $this->assertEquals(5,$conf->instance['maxChildren']);
        $this->assertEquals('127.0.0.1',$conf->instance['address']);
        $this->assertEquals(8124,$conf->instance['port']);
        $this->assertNull($conf->urimap);
        $this->assertType('array',$conf->includes);
    }

    function testGetDBPoolConf()
    {
        $this->cf->setXmlSrc(dirname(__FILE__).'/seraphp_test_config.xml');
        $conf = $this->cf->getConf('dbpool');
        $this->assertThat($conf,$this->IsInstanceOf('Config'));
        $this->assertEquals('/home/peter/workspace/seraphp',$conf->pidpath);
        $this->assertEquals(5,$conf->instance['maxChildren']);
        $this->assertEquals('127.0.0.1',$conf->instance['address']);
        $this->assertEquals(8125,$conf->instance['port']);
        $this->assertNull($conf->urimap);
        $this->assertType('array',$conf->includes);
    }
}