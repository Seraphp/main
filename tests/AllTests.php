<?php
require_once 'PHPUnit/Autoload.php';
require_once 'Policy/AllTests.php';
require_once 'Server/AllTests.php';
require_once 'Comm/AllTests.php';
require_once 'Log/AllTests.php';
require_once 'Exceptions/AllTests.php';
require_once 'Process/AllTests.php';

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
        $suite->addTest(Process_AllTests::suite());
        PHP_CodeCoverage_Filter::getInstance()->addDirectoryToBlacklist(
            '/usr/share/php'
        );
        PHP_CodeCoverage_Filter::getInstance()->addDirectoryToBlacklist(
            '/opt/ZendFramework-1.10.5'
        );
        PHP_CodeCoverage_Filter::getInstance()->addDirectoryToBlacklist(
            '/opt/ZendFramework'
        );
        PHP_CodeCoverage_Filter::getInstance()->addDirectoryToBlacklist(
            '/opt/cruisecontrol/projects/seraphp/source/tests'
        );
        return $suite;
    }
}