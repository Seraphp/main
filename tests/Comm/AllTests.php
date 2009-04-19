<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
// @codeCoverageIgnoreStart
require_once 'PHPUnit/Framework.php';
require_once 'Ipc/AllTests.php';
require_once 'Http/AllTests.php';
require_once 'RequestFactoryTest.php';
require_once 'SocketTest.php';
/**
 * Class documentation
 */
class Comm_AllTests{

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Seraphp Comm');
        $suite->addTest(Ipc_AllTests::suite());
        $suite->addTest(Http_AllTests::suite());
        $suite->addTestSuite('SocketTest');
        $suite->addTestSuite('RequestFactoryTest');
        return $suite;
    }
}
// @codeCoverageIgnoreEnd