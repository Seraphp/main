<?php
require_once 'Process/System.class.php';
use \Seraphp\Process\System;

class SystemTest extends PHPUnit_Framework_TestCase
{
    function setUp()
    {
        if (file_exists('\tmp\testFIFO')) {
           unlink('\tmp\testFIFO');
        }
        if (file_exists('\tmp\testNode')) {
           unlink('\tmp\testNode');
        }
    }

    function tearDown()
    {
        $this->setUp();
    }

    function testGetInstance()
    {
        $inst = System::getInstance();
        $this->assertInstanceOf(
            '\Seraphp\Process\System', $inst
        );
        $this->assertSame($inst, System::getInstance());
    }

    function testGetCwd()
    {
        $this->assertEquals(getcwd(), System::getInstance()->getCWD());
    }

    function testGetAccess()
    {
        $access = System::getInstance()->checkAccess(
            getcwd().'/tests/AllTests.php'
        );
        $this->assertInternalType(
            'array',
            $access
        );
        $this->assertTrue($access['read']);
        $this->assertTrue($access['write']);
        $this->assertInstanceOf(
            '\Seraphp\Exceptions\ProcessException', $access['exec']
        );
    }

    function testGetResourceLimit()
    {
        $limits = System::getInstance()->getResourceLimits();
        $this->assertInternalType('array', $limits);
        $this->assertArrayHasKey('soft core', $limits);
    }

    function testIsTerminal()
    {
        $this->assertFalse(System::getInstance()->isTerminal(STDOUT));
        $this->assertTrue(
            System::getInstance()->isTerminal(fopen('/dev/tty7', 'r+'))
        );
        $this->setExpectedException(
            '\Seraphp\Exceptions\ProcessException',
            'Argument is not a valid file descriptor'
        );
        System::getInstance()->isTerminal(70);
    }

    function testCreateFIFO()
    {
        $fifo = '/tmp/testFifo';
        $this->assertFalse(file_exists($fifo));
        System::getInstance()->createFIFO($fifo, 0700);
        $this->assertTrue(file_exists($fifo));
        unlink($fifo);
        $this->assertFalse(file_exists($fifo));
    }

    /*/**
     * Because test runing with ROOT rights, this test will not fail
     *
    function testCreateInvalidFIFO()
    {
        $fifo = '/etc/testFifo';
        $this->assertFalse(file_exists($fifo));
        $this->setExpectedException(
            '\Seraphp\Exceptions\ProcessException'
        );
        System::getInstance()->createFIFO($fifo, 0700);
        $this->assertFalse(file_exists($fifo));
    }*/

    function testCreateNode()
    {
        $node = '/tmp/testNode';
        $this->assertFalse(file_exists($node));
        System::getInstance()->createNode($node);
        $this->assertTrue(file_exists($node));
        unlink($node);
        $this->assertFalse(file_exists($node));
    }

    /*/**
     * Because test runing with ROOT rights, this test will not fail
     *
    function testCreateInvalidNode()
    {
        $node = '/etc/testNode';
        $this->assertFalse(file_exists($node));
        $this->setExpectedException(
            '\Seraphp\Exceptions\ProcessException'
        );
        System::getInstance()->createNode($node);
        $this->assertFalse(file_exists($node));
    }*/

    function testGetTerminalName()
    {
        $path = '/dev/tty7';
        $this->assertEquals(
            $path,
            System::getInstance()->getTerminalName(fopen($path, "r+"))
        );
    }

    function testGetNONTerminalName()
    {
        $this->setExpectedException(
            '\Seraphp\Exceptions\ProcessException',
            'File descriptor is not a terminal'
        );
        System::getInstance()->getTerminalName(STDOUT);
    }

    function testGetInvalidTerminalName()
    {
        $this->setExpectedException(
            '\Seraphp\Exceptions\ProcessException',
            'Argument is not a valid file descriptor'
        );
        System::getInstance()->getTerminalName(42);
    }

    function testGetSystemInfo()
    {
        $info = System::getInstance()->getSystemInfo();
        $this->assertInternalType('array', $info);
        $this->assertArrayHasKey('sysname', $info);
        $this->assertArrayHasKey('nodename', $info);
        $this->assertArrayHasKey('release', $info);
        $this->assertArrayHasKey('version', $info);
        $this->assertArrayHasKey('machine', $info);
        $this->assertArrayHasKey('domainname', $info);
    }

    function testIsVALIDSignalSupported()
    {
        $this->assertTrue(
            System::getInstance()->isSignalSupported('SIGUSR1')
        );
    }

    function testIsDUMBSignalSupported()
    {
        $this->assertFalse(
            System::getInstance()->isSignalSupported('SIGHELLO')
        );
    }
}