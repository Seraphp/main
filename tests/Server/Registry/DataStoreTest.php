<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */

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
        $this->_store = new \Seraphp\Server\Registry\DataStore();
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
        $engine = $this->getMock(
            '\Seraphp\Server\Registry\PackedFileDataStore'
        );
        $engine->expects($this->once())->method('setUp');
        $engine->expects($this->once())->method('load');
        $this->_store->setEngine($engine);
    }

    function testGetEngine()
    {
        $this->_store->setEngine(
            new \Seraphp\Server\Registry\PackedFileDataStore()
        );
        $this->assertEquals(
            'Seraphp\Server\Registry\PackedFileDataStore',
            $this->_store->getEngineType()
        );
    }

    function tearDown()
    {
        if (isset($this->_store)) {
            unset($this->_store);
        }
    }
}