<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
require_once 'PHPUnit/Framework.php';
require_once 'Ipc/AllTests.php';
/**
 * Class documentation
 */
class Comm_AllTests{

    public static function suite()
    {
        // @codeCoverageIgnoreStart
        $suite = new PHPUnit_Framework_TestSuite('Phaser Comm');
        $suite->addTest(Ipc_AllTests::suite());
        return $suite;
        // @codeCoverageIgnoreEnd
    }
}
?>