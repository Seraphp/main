<?php
require_once 'Process/Factory.class.php';
use \Seraphp\Process\Factory;

class FactoryTest extends PHPUnit_Framework_TestCase
{
    function testCreation()
    {
        $this->assertInstanceOf(
        	'\Seraphp\Process\Process',
            Factory::create('process',getmypid())
        );
        $this->assertInstanceOf(
        	'\Seraphp\Process\Group',
            Factory::create('group','peter')
        );
        $this->assertInstanceOf(
        	'\Seraphp\Process\User',
            Factory::create('user','peter')
        );
    }

    function testCreateMany()
    {
        $result = Factory::createMany('user', array('1000','peter','judit'));
        $this->assertInstanceOf(
            '\Seraphp\Process\User', $result[0]
        );
        $this->assertInstanceOf(
            '\Seraphp\Process\User', $result[1]
        );
        $this->assertInstanceOf(
            '\Seraphp\Process\User', $result[2]
        );
    }
}