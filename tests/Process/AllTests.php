<?php
require_once 'FactoryTest.class.php';
require_once 'ProcessTest.class.php';
require_once 'SystemTest.class.php';
require_once 'UserTest.class.php';
require_once 'GroupTest.class.php';

class Process_AllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Process');
        $suite->addTestSuite('FactoryTest');
        $suite->addTestSuite('ProcessTest');
        $suite->addTestSuite('GroupTest');
        $suite->addTestSuite('SystemTest');
        $suite->addTestSuite('UserTest');
        return $suite;
    }
}