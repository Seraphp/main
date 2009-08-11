<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
require_once 'PHPUnit/Framework.php';
require_once 'Server/Registry/Registry.class.php';
/**
 * Class documentation
 */
class RegistryTest extends PHPUnit_Framework_TestCase{

    private $reg = null;

    function setUp()
    {
        $this->reg = Registry::getInstance();
    }

    function testRegistryIsSingleton()
    {
        $this->assertThat($this->reg, $this->IsInstanceOf('Singleton'));
        $this->assertSame($this->reg, Registry::getInstance());
    }

    function testCloningDisabled()
    {
        $this->setExpectedException('Exception');
        $newReg = clone $this->reg;
    }

    function testAddingEngine()
    {
        //TODO: adding simply the real Data Store here stops testing script. Figure it out!
        require_once 'Server/Registry/PackedFileDataStore.class.php';
        $engine = $this->getMock('PackedFileDataStore');
        $this->reg = Registry::getInstance($engine);
        $this->assertThat($this->reg, $this->IsInstanceOf('Registry'));
    }

    function testEmptyRegistryKeyIsInvalid()
    {
        $this->assertFalse(isset($this->reg->somekey));
    }

    function testEmptyRegistryKeyReturnsNull()
    {
        $this->assertNull($this->reg->somekey);
    }


    function testRegistryKeyValid()
    {
        $this->reg->somekey = array('foo'=>array('bar'=>'somevalue'));
        $this->assertTrue(isset($this->reg->somekey));
    }

    function testRegistryValueIsReference()
    {
        $some_key = array('foo'=>array('bar'=>'somevalue'));
        $this->reg->some_key = $some_key;
        $this->assertSame($this->reg->some_key, $some_key);
    }
}