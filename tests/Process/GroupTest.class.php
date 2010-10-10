<?php
require_once 'Process/Group.class.php';
use \Seraphp\Process\Group;

class GroupTest extends PHPUnit_Framework_TestCase
{
    function testCreationById()
    {
        $this->assertInstanceOf('\Seraphp\Process\Group', new Group(100));
    }

    function testCreationByName()
    {
        $this->assertInstanceOf('\Seraphp\Process\Group', new Group('users'));
    }

    function testCreationIvalid()
    {
        $this->setExpectedException(
        	'\Seraphp\Exceptions\ProcessException',
            'No groupid provided'
        );
        $this->assertInstanceOf('\Seraphp\Process\Group', new Group(false));
    }

    function testGetId()
    {
        $grp = new Group('users');
        $this->assertEquals(100, $grp->getId());
    }

    function testGetName()
    {
        $grp = new Group(100);
        $this->assertEquals('users', $grp->getName());
    }

    function testGetMembers()
    {
        $grp = new Group(100);
        $members = $grp->getMembers();
        $this->assertType('array', $grp->getMembers());
        $this->assertInstanceOf('\Seraphp\Process\User', $members[0]);
    }

    function testIterator()
    {
        $grp = new Group(100);
        $this->assertInstanceOf('\Iterator', $grp);
        foreach ($grp as $name=>$user) {
            $this->assertInstanceOf('\Seraphp\Process\User', $user);
        }
    }
}