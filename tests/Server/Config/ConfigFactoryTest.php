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
class ConfigFactoryTest extends PHPUnit_Framework_TestCase
{
    private $_cf;

    function setUp()
    {
        $this->_cf = ConfigFactory::getInstance();
    }

    function testFactoryIsSingleton()
    {
        $this->assertThat($this->_cf, $this->IsInstanceOf('ConfigFactory'));
        $this->assertSame($this->_cf, ConfigFactory::getInstance());
    }


    function testCloningDisabled()
    {
        $cF= new ReflectionObject($this->_cf);
        $this->assertTrue($cF->getMethod('__clone')->isPrivate());
    }

    function testGetMainConf()
    {
        $this->_cf->setXmlSrc(dirname(__FILE__).'/seraphp_test_config.xml');
        $conf = $this->_cf->getConf('main');
        $this->assertThat($conf, $this->IsInstanceOf('SimpleXMLElement'));
        $this->assertEquals(
            '/home/peter/workspace/seraphp', (string)$conf['pidpath']
        );
        $this->assertEquals(5, (int)$conf->instance->maxChildren);
        $this->assertEquals('127.0.0.1', (string)$conf->instance->address);
        $this->assertEquals(8123, (int)$conf->instance->port);
        $this->assertType('SimpleXMLElement', $conf->urimap);
        $this->assertType('SimpleXMLElement', $conf->includes);
    }

    function testGetSessionConf()
    {
        $this->_cf->setXmlSrc(dirname(__FILE__).'/seraphp_test_config.xml');
        $conf = $this->_cf->getConf('session');
        $this->assertThat($conf, $this->IsInstanceOf('SimpleXMLElement'));
        $this->assertEquals(
            '/home/peter/workspace/seraphp', (string)$conf['pidpath']
        );
        $this->assertEquals(5, (int)$conf->instance->maxChildren);
        $this->assertEquals('127.0.0.1', (string)$conf->instance->address);
        $this->assertEquals(8124, (int)$conf->instance->port);
        $this->assertType('SimpleXMLElement', $conf->includes);
    }

    function testGetDBPoolConf()
    {
        $this->_cf->setXmlSrc(dirname(__FILE__).'/seraphp_test_config.xml');
        $conf = $this->_cf->getConf('dbpool');
        $this->assertThat($conf, $this->IsInstanceOf('SimpleXMLElement'));
        $this->assertEquals(
            '/home/peter/workspace/seraphp', (string)$conf['pidpath']
        );
        $this->assertEquals(5, (int)$conf->instance->maxChildren);
        $this->assertEquals('127.0.0.1', (string)$conf->instance->address);
        $this->assertEquals(8125, (int)$conf->instance->port);
        $this->assertType('SimpleXMLElement', $conf->includes);
    }
}