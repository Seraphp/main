<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */

require_once 'Server/Registry/Registry.class.php';
/**
 * Class documentation
 */
class RegistryTest extends PHPUnit_Framework_TestCase
{

    private $_reg = null;

    function setUp()
    {
        $this->_reg = \Seraphp\Server\Registry\Registry::getInstance();
    }

    function testRegistryIsSingleton()
    {
        $this->assertThat(
            $this->_reg, $this->IsInstanceOf('\Seraphp\Singleton')
        );
        $this->assertSame(
            $this->_reg, \Seraphp\Server\Registry\Registry::getInstance()
        );
    }

    function testCloningDisabled()
    {
        $this->setExpectedException('\Exception');
        $newReg = clone $this->_reg;
    }

    function testAddingEngine()
    {
        //TODO: adding simply the real Data Store here stops testing script.
        //Figure it out!
        require_once 'Server/Registry/PackedFileDataStore.class.php';
        $engine = $this->getMock(
            '\Seraphp\Server\Registry\PackedFileDataStore'
        );
        $this->_reg = \Seraphp\Server\Registry\Registry::getInstance($engine);
        $this->assertThat(
            $this->_reg,
            $this->IsInstanceOf('\Seraphp\Server\Registry\Registry')
        );
    }

    function testEmptyRegistryKeyIsInvalid()
    {
        $this->assertFalse(isset($this->_reg->somekey));
    }

    function testEmptyRegistryKeyReturnsNull()
    {
        $this->assertNull($this->_reg->somekey);
    }


    function testRegistryKeyValid()
    {
        $this->_reg->someKey = array('foo'=>array('bar'=>'somevalue'));
        $this->assertTrue(isset($this->_reg->someKey));
    }

    function testRegistryValueIsReference()
    {
        $someKey = array('foo'=>array('bar'=>'somevalue'));
        $this->_reg->someKey = $someKey;
        $this->assertSame($this->_reg->someKey, $someKey);
    }

    function tearDown()
    {
        unset($this->_reg);
    }
}