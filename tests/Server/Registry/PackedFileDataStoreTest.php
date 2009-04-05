<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
require_once 'PHPUnit/Framework.php';
require_once 'Server/Registry/PackedFileDataStore.class.php';
/**
 * Class documentation
 */
class PackedFileDataStoreTest extends PHPUnit_Framework_TestCase{

    private $Testdata;

    function setUp()
    {
        $this->Testdata = array('ÁRVÍZTŰRŐ TÜKÖRFÚRÓGÉP árvíztűrő', 'tükörfúrógép');
    }

    function testInitNoPath()
    {
        $store = new PackedFileDataStore();
        $this->assertTrue($store->init());
        $this->assertNotEquals('',$store->getPath());
        $this->assertFileExists($store->getPath());
    }

    function testInitWithPath()
    {
        $store = new PackedFileDataStore('./testDataFile.gz');
        $this->assertEquals(realpath('./testDataFile.gz'), $store->getPath());
        $this->assertFileExists($store->getPath());
    }

    function testConstructorNoPath()
    {
        $store = new PackedFileDataStore();
        $this->assertEquals('', $store->getPath());
    }

    function testConstructorWithPath()
    {
        $store = new PackedFileDataStore('./testDataFile.gz');
        $this->assertEquals(realpath('./testDataFile.gz'),$store->getPath());
        $this->assertFileExists($store->getPath());
    }

    function testSave()
    {
        $store = new PackedFileDataStore();
        $store->init();
        $this->assertTrue($store->save($this->Testdata));
        $this->assertThat(
            $this->fileExists($store->getPath()),
            $this->logicalAnd(),
            filesize($store->getPath()),
            $this->greaterThan(0)
        );
    }

    function testLoadWithNoFile()
    {
        $store = new PackedFileDataStore();
        $store->init();
        $this->assertTrue($store->save($this->Testdata));
        $this->assertEquals($this->Testdata, $store->load());
    }

    function testLoadWithFile()
    {
        $store = new PackedFileDataStore();
        $store->init('./test1.gz');
        $this->assertTrue($store->save($this->Testdata));
        $store->init('./test2.gz');
        $store->init('./test1.gz');
        $this->assertEquals($this->Testdata, $store->load());
    }
}