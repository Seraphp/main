<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
require_once 'PHPUnit/Framework.php';
require_once 'tests/Server/AppServerTest.php';
require_once 'tests/Server/Registry/AllTests.php';
/**
 * Class documentation
 */
class Server_AllTests{

    public static function suite()
    {
        // @codeCoverageIgnoreStart
        $suite = new PHPUnit_Framework_TestSuite('Phaser Server');
        $suite->addTestSuite('AppServerTest');
        $suite->addTest(Registry_AllTests::suite());
        return $suite;
        // @codeCoverageIgnoreEnd
    }
}
?>