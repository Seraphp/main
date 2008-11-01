<?php
ini_set('include_path',DEFAULT_INCLUDE_PATH.PATH_SEPARATOR.'/home/peter/workspace/phaser');

require_once 'PHPUnit/Framework.php';
require_once 'tests/Policy/AllTests.php';

class AllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Phaser');
        $suite->addTest(Policy_AllTests::suite());
        return $suite;
    }
}
?>