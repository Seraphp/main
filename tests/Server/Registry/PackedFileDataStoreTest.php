<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */

require_once 'Server/Registry/PackedFileDataStore.class.php';
require_once 'vfsStream/vfsStream.php';
/**
 * Class documentation
 */
class PackedFileDataStoreTest extends PHPUnit_Framework_TestCase
{

    private $_testData = null;
    private $_createdFiles = null;
    private $_store = null;

    function setUp()
    {
        $this->_createdFiles = array();
        $this->_testData = array(
            'ÁRVÍZTŰRŐ TÜKÖRFÚRÓGÉP', 'árvíztűrő tükörfúrógép'
        );
    }

    function testInitNoPath()
    {
        $this->_store = new PackedFileDataStore();
        $this->assertTrue($this->_store->setUp());
        $this->assertNotEquals('', $this->_store->getPath());
        $this->assertFileExists($this->_store->getPath());
        array_push($this->_createdFiles, $this->_store->getPath());
    }

    function testInitWithPath()
    {
        $this->_store = new PackedFileDataStore('./testDataFile.gz');
        $this->assertEquals(
            realpath('./testDataFile.gz'), $this->_store->getPath()
        );
        $this->assertFileExists($this->_store->getPath());
        array_push($this->_createdFiles, $this->_store->getPath());
    }

    function testSave()
    {
        $this->_store = new PackedFileDataStore();
        $this->_store->setUp();
        $this->assertTrue($this->_store->save($this->_testData));
        $this->assertThat(
            $this->fileExists($this->_store->getPath()),
            $this->logicalAnd(),
            filesize($this->_store->getPath()),
            $this->greaterThan(0)
        );
        array_push($this->_createdFiles, $this->_store->getPath());
    }

    function testLoadWithNoFile()
    {
        $this->_store = new PackedFileDataStore();
        $this->_store->setUp();
        $tempFile = $this->_store->getPath();
        array_push($this->_createdFiles, $this->_store->getPath());
        $this->assertTrue($this->_store->save($this->_testData));
        $this->assertEquals($this->_testData, $this->_store->load($tempFile));
    }

    function testLoadWithFile()
    {
        $this->_store = new PackedFileDataStore();
        $this->_store->setUp('./test1.gz');
        array_push($this->_createdFiles, $this->_store->getPath());
        $this->assertTrue($this->_store->save($this->_testData));
        $this->assertEquals(
            $this->_testData, $this->_store->load('./test1.gz')
        );
    }

    function tearDown()
    {
        unset($this->_store);
        foreach ($this->_createdFiles as $file) {
            if (file_exists($file)) unlink($file);
        }
    }
}