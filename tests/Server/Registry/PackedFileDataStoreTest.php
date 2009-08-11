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
    private $createdFiles;

    function setUp()
    {
        $this->createdFiles = array();
        $this->Testdata = array('ÁRVÍZTŰRŐ TÜKÖRFÚRÓGÉP', 'árvíztűrő tükörfúrógép');
    }

    function testInitNoPath()
    {
        $store = new PackedFileDataStore();
        $this->assertTrue($store->init());
        $this->assertNotEquals('',$store->getPath());
        $this->assertFileExists($store->getPath());
        array_push($this->createdFiles, $store->getPath());
    }

    function testInitWithPath()
    {
        $store = new PackedFileDataStore('./testDataFile.gz');
        $this->assertEquals(realpath('./testDataFile.gz'), $store->getPath());
        $this->assertFileExists($store->getPath());
        array_push($this->createdFiles, $store->getPath());
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
        array_push($this->createdFiles, $store->getPath());
    }

    function testLoadWithNoFile()
    {
        $store = new PackedFileDataStore();
        $store->init();
        array_push($this->createdFiles, $store->getPath());
        $this->assertTrue($store->save($this->Testdata));
        $this->assertEquals($this->Testdata, $store->load());
    }

    function testLoadWithFile()
    {
        $store = new PackedFileDataStore();
        $store->init('./test1.gz');
        array_push($this->createdFiles, $store->getPath());
        $this->assertTrue($store->save($this->Testdata));
        $this->assertEquals($this->Testdata, $store->load('./test1.gz'));
    }

    function tearDown()
    {
        foreach ($this->createdFiles as $file) {
            if (file_exists($file)) unlink($file);
        }
    }
}