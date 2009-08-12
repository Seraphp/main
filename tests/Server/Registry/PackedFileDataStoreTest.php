<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
require_once 'PHPUnit/Framework.php';
require_once 'Server/Registry/PackedFileDataStore.class.php';
require_once 'vfsStream/vfsStream.php';
/**
 * Class documentation
 */
class PackedFileDataStoreTest extends PHPUnit_Framework_TestCase{

    private $testData;
    private $createdFiles;

    function setUp()
    {
        $this->createdFiles = array();
        $this->testData = array('ÁRVÍZTŰRŐ TÜKÖRFÚRÓGÉP', 'árvíztűrő tükörfúrógép');
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
        $this->assertTrue($store->save($this->testData));
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
        $tempFile = $store->getPath();
        array_push($this->createdFiles, $store->getPath());
        $this->assertTrue($store->save($this->testData));
        $this->assertEquals($this->testData, $store->load($tempFile));
    }

    function testLoadWithFile()
    {
        $store = new PackedFileDataStore();
        $store->init('./test1.gz');
        array_push($this->createdFiles, $store->getPath());
        $this->assertTrue($store->save($this->testData));
        $this->assertEquals($this->testData, $store->load('./test1.gz'));
    }

    function tearDown()
    {
        foreach ($this->createdFiles as $file) {
            if (file_exists($file)) unlink($file);
        }
    }
}