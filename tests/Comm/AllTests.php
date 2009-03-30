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
require_once 'SocketTest.php';
/**
 * Class documentation
 */
class Comm_AllTests{

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Seraphp Comm');
        $suite->addTest(Ipc_AllTests::suite());
        $suite->addTestSuite('SocketTest');
        return $suite;
    }
}
// @codeCoverageIgnoreEnd