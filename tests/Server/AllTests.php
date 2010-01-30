<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
require_once 'PHPUnit/Framework.php';
require_once 'AppServerTest.php';
require_once 'DefaultEngineTest.php';
require_once 'Registry/AllTests.php';
require_once 'Config/AllTests.php';
/**
 * Class documentation
 */
class Server_AllTests
{

    public static function suite()
    {

        $suite = new PHPUnit_Framework_TestSuite('Seraphp Server');
        $suite->addTest(Registry_AllTests::suite());
        $suite->addTest(Config_AllTests::suite());
        $suite->addTestSuite('DefaultEngineTest');
        $suite->addTestSuite('AppServerTest');
        return $suite;
    }
}