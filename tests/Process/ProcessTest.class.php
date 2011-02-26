<?php
require_once 'Process/Process.class.php';
use \Seraphp\Process\Process;

class ProcessTest extends PHPUnit_Framework_TestCase
{
    function testCreation()
    {
        $this->assertInstanceOf(
        	'\Seraphp\Process\Process', new Process()
        );
        $this->assertInstanceOf(
        	'\Seraphp\Process\Process', new Process(getmypid())
        );
        $this->setExpectedException(
        	'\Seraphp\Exceptions\ProcessException',
            'Only integer process id accepted'
        );
        new Process(true);
    }

    function testGetId()
    {
        $proc = new Process();
        $this->assertInternalType('integer', $proc->getId());
    }

    function testGetUser()
    {
        $proc = new Process();
        $this->assertInstanceOf('\Seraphp\Process\User', $proc->getUser());
        $this->assertInstanceOf('\Seraphp\Process\User', $proc->getUser(true));
    }

    function testGetGroup()
    {
        $proc = new Process();
        $this->assertInstanceOf('\Seraphp\Process\Group', $proc->getGroup());
        $this->assertInstanceOf('\Seraphp\Process\Group', $proc->getGroup(true));
    }

    function testGetParent()
    {
        $proc = new Process();
        $this->assertInstanceOf('\Seraphp\Process\Process', $proc->getParent());
    }

    function testGetProcGroup()
    {
        $proc = new Process();
        $this->assertInternalType('integer', $proc->getProcGrp());
    }

    function testGetSessionID()
    {
        $proc = new Process();
        $this->assertInternalType('integer', $proc->getSessionID());
    }

    function testGetTimes()
    {
        $proc = new Process();
        $this->assertInternalType('array', $proc->getTimes());
        $this->assertArrayHasKey('ticks', $proc->getTimes());
        $this->assertArrayHasKey('utime', $proc->getTimes());
        $this->assertArrayHasKey('stime', $proc->getTimes());
        $this->assertArrayHasKey('cutime', $proc->getTimes());
        $this->assertArrayHasKey('cstime', $proc->getTimes());
    }
}