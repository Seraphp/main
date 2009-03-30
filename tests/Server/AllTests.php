<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
// @codeCoverageIgnoreStart
require_once 'PHPUnit/Framework.php';
require_once 'Server/AppServerTest.php';
require_once 'DataStoreTest.php';
require_once 'Registry/AllTests.php';
require_once 'Config/AllTests.php';
/**
 * Class documentation
 */
class Server_AllTests{

    public static function suite()
    {

        $suite = new PHPUnit_Framework_TestSuite('Seraphp Server');
        $suite->addTestSuite('AppServerTest');
        $suite->addTestSuite('DataStoreTest');
        $suite->addTest(Registry_AllTests::suite());
        $suite->addTest(Config_AllTests::suite());
        return $suite;

    }
}
// @codeCoverageIgnoreEnd