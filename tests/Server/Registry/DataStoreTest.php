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
class DataStoreTest extends PHPUnit_Framework_TestCase{

    private $reg = null;
    private $path;

    function setUp()
    {
        $this->store = new DataStore();
    }

    function testEmptyKeyIsInvalid()
    {
        $this->assertFalse(isset($this->store->somekey));
    }

    function testEmptyKeyReturnsNull()
    {
        $this->assertNull($this->store->somekey);
    }

    function testProtectedPropertyIsFalse()
    {
        $this->store->_test = 'something';
        $this->assertFalse(isset($this->store->_test));
        $this->assertEquals(null, $this->store->_test);
    }

    function testKeyValid()
    {
        $this->store->somekey = array('foo'=>array('bar'=>'somevalue'));
        $this->assertTrue(isset($this->store->somekey));
    }

    function testOverWrite()
    {
        $this->store->test = 'foo';
        $this->assertFalse($this->store->setOverwrite(false));
        $this->assertEquals('foo', $this->store->test);
    }

    function testValueIsReference()
    {
        $some_key = array('foo'=>array('bar'=>'somevalue'));
        $this->store->some_key = $some_key;
        $this->assertSame($this->store->some_key, $some_key);
    }

    function testSetEngine()
    {
        $this->assertFalse($this->store->getEngineType());
        $engine = $this->getMock('PackedFileDataStore');
        $engine->expects($this->once())->method('init');
        $engine->expects($this->once())->method('load');
        $this->store->setEngine($engine);
    }

    function testGetEngine()
    {
        $this->store->setEngine(new PackedFileDataStore());
        $this->assertEquals('PackedFileDataStore', $this->store->getEngineType());
    }

    function testDestructor()
    {
        $engine = $this->getMock('PackedFileDataStore');
        $engine->expects($this->once())->method('save');
        $this->store->setEngine($engine);
        unset($this->store);
    }
}