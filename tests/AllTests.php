<?php
ini_set('include_path',DEFAULT_INCLUDE_PATH.PATH_SEPARATOR.'tests/');

require_once 'PHPUnit/Framework.php';
require_once 'Policy/AllTests.php';
require_once 'Server/AllTests.php';
require_once 'Comm/AllTests.php';

class AllTests
{
    public static function suite()
    {
        // @codeCoverageIgnoreStart
        $suite = new PHPUnit_Framework_TestSuite('Seraphp');
        $suite->addTest(Policy_AllTests::suite());
        $suite->addTest(Server_AllTests::suite());
        $suite->addTest(Comm_AllTests::suite());
        PHPUnit_Util_Filter::removeDirectoryFromWhiteList('/home/peter/workspace/seraphp/tests/');
        return $suite;
        // @codeCoverageIgnoreEnd
    }
}
?>