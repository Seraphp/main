<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */

require_once 'Server/Config/Config.class.php';
/**
 * Class documentation
 */
class ConfigTest extends PHPUnit_Framework_TestCase
{
    private $_conf;
    private $_xml;

    function setUp()
    {
        $this->_conf = new Config('<test><foo/><bar/></test>');
        $this->_xml = simplexml_load_file(
            getcwd().'/tests/Server/Config/seraphp_test_config.xml',
            'Config'
        );
    }

    function testEmptyConfigKeyIsInvalid()
    {
        $this->assertFalse(isset($this->_conf->somekey));
    }

    function testConfigKeyValid()
    {
        $this->_conf->somekey->foo->bar='somevalue';
        $this->assertTrue(isset($this->_conf->somekey));
    }

    function testXSearch()
    {
        $result = $this->_conf->xsearch('/test');
        $this->assertType('array', $result);
        $this->assertType('object', $result[0]);
        $this->assertObjectHasAttribute('foo', $result[0]);
        $this->assertObjectHasAttribute('bar', $result[0]);
    }

    function testXSearchFromNode()
    {
        $result = $this->_xml->xsearch('//srph:server', $this->_xml->servers);
        $this->assertType('array', $result);
        $this->assertObjectHasAttribute('instance', $result[0]);
    }

    function tearDown()
    {
        unset($this->_conf);
        unset($this->_xml);
    }
}