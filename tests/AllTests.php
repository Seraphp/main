<?php
ini_set('include_path',DEFAULT_INCLUDE_PATH.PATH_SEPARATOR.'/home/peter/workspace/phaser');

require_once 'PHPUnit/Framework.php';
require_once 'tests/Policy/AllTests.php';
require_once 'tests/Server/AllTests.php';

class AllTests
{
    public static function suite()
    {
        // @codeCoverageIgnoreStart
        $suite = new PHPUnit_Framework_TestSuite('Phaser');
        $suite->addTest(Policy_AllTests::suite());
        $suite->addTest(Server_AllTests::suite());
        PHPUnit_Util_Filter::removeDirectoryFromWhiteList('/home/peter/workspace/phaser/tests/');
        return $suite;
        // @codeCoverageIgnoreEnd
    }
}
?>