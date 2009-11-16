<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
require_once 'PHPUnit/Framework.php';
require_once 'Server/Registry/DataStore.class.php';
require_once 'Server/Registry/PackedFileDataStore.class.php';
/**
 * Class documentation
 */
class DataStoreTest extends PHPUnit_Framework_TestCase
{
    private $_store = null;

    function setUp()
    {
        $this->_store = new DataStore();
    }

    function testEmptyKeyIsInvalid()
    {
        $this->assertFalse(isset($this->_store->somekey));
    }

    function testEmptyKeyReturnsNull()
    {
        $this->assertNull($this->_store->somekey);
    }

    function testProtectedPropertyIsFalse()
    {
        $this->_store->_test = 'something';
        $this->assertFalse(isset($this->_store->_test));
        $this->assertEquals(null, $this->_store->_test);
    }

    function testKeyValid()
    {
        $this->_store->somekey = array('foo'=>array('bar'=>'somevalue'));
        $this->assertTrue(isset($this->_store->somekey));
    }

    function testOverWrite()
    {
        $this->_store->test = 'foo';
        $this->assertFalse($this->_store->setOverwrite(false));
        $this->assertEquals('foo', $this->_store->test);
    }

    function testValueIsReference()
    {
        $someKey = array('foo'=>array('bar'=>'somevalue'));
        $this->_store->someKey = $someKey;
        $this->assertSame($this->_store->someKey, $someKey);
    }

    function testSetEngine()
    {
        $this->assertFalse($this->_store->getEngineType());
        $engine = $this->getMock('PackedFileDataStore');
        $engine->expects($this->once())->method('init');
        $engine->expects($this->once())->method('load');
        $this->_store->setEngine($engine);
    }

    function testGetEngine()
    {
        $this->_store->setEngine(new PackedFileDataStore());
        $this->assertEquals(
            'PackedFileDataStore', $this->_store->getEngineType()
        );
    }

    function testDestructor()
    {
        $engine = $this->getMock('PackedFileDataStore');
        $engine->expects($this->once())->method('save');
        $this->_store->setEngine($engine);
        unset($this->_store);
    }
}