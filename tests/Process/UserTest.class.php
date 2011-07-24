<?php
require_once 'Process/User.class.php';
use \Seraphp\Process\User;

class UserTest extends PHPUnit_Framework_TestCase
{
    function testCreationByName()
    {
        $this->assertInstanceOf(
            '\Seraphp\Process\User',
            new User('peter')
        );
    }

    function testCreationById()
    {
        $this->assertInstanceOf(
            '\Seraphp\Process\User',
            new User(1000)
        );
    }

    function testCreationWrongly()
    {
        $this->setExpectedException(
            '\Seraphp\Exceptions\ProcessException',
            'No user id provided'
        );
        new User(true);
    }

    function testGetName()
    {
        $usr = new User('peter');
        $this->assertEquals('peter', $usr->getName());
    }

    function testGetRealName()
    {
        $usr = new User('peter');
        $this->assertEquals('Nagy Péter', $usr->getName(true));
    }

    function testGetId()
    {
        $usr = new User('peter');
        $this->assertEquals(1000, $usr->getId());
    }

    function testGetGroup()
    {
        $usr = new User('peter');
        $this->assertEquals('peter', $usr->getGroup()->getName());
    }

    function testGetShell()
    {
        $usr = new User('peter');
        $this->assertEquals('/bin/bash', $usr->getShell());
    }

    function testGetDir()
    {
        $usr = new User('peter');
        $this->assertInstanceOf('\Directory', $usr->getDir());
        $this->assertEquals('/home/peter', $usr->getDir()->path);
    }
}