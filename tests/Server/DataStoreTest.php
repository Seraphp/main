<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
require_once 'PHPUnit/Framework.php';
require_once 'Server/DataStore.class.php';
/**
 * Class documentation
 */
class DataStoreTest extends PHPUnit_Framework_TestCase{

    private $reg = null;

    function setUp()
    {
        $this->store = new DataStore();
    }

    function testEmptyKeyIsInvalid()
    {
        $this->assertFalse($this->store->isExists('somekey'));
    }

    function testEmptyKeyReturnsNull()
    {
        $this->assertNull($this->store->somekey);
    }

    function testKeyValid()
    {
        $this->store->somekey = array('foo'=>array('bar'=>'somevalue'));
        $this->assertTrue($this->store->isExists('somekey'));
    }



    function testValueIsReference()
    {
        $some_key = array('foo'=>array('bar'=>'somevalue'));
        $this->store->some_key = $some_key;
        $this->assertSame($this->store->some_key, $some_key);
    }
}
?>