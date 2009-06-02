<?php
//ini_set('include_path',DEFAULT_INCLUDE_PATH.PATH_SEPARATOR.'tests/');
require_once 'PHPUnit/Framework.php';
require_once 'tests/Policy/AllTests.php';
require_once 'tests/Server/AllTests.php';
require_once 'tests/Comm/AllTests.php';
require_once 'tests/Log/AllTests.php';
require_once 'tests/Exceptions/AllTests.php';

class AllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Seraphp');
        $suite->addTest(Policy_AllTests::suite());
        $suite->addTest(Server_AllTests::suite());
        $suite->addTest(Comm_AllTests::suite());
        $suite->addTest(Exceptions_AllTests::suite());
        $suite->addTest(Log_AllTests::suite());
        PHPUnit_Util_Filter::addDirectoryToFilter('/usr/share/php');
        PHPUnit_Util_Filter::addDirectoryToFilter('/opt/cruisecontrol/projects/seraphp/source/tests');
        return $suite;
    }
}