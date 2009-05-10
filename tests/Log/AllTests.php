<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
// @codeCoverageIgnoreStart
require_once 'PHPUnit/Framework.php';
require_once 'LogFactoryTest.php';
/**
 * Class documentation
 */
class Log_AllTests{

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Seraphp Log');
        $suite->addTestSuite('LogFactoryTest');
        return $suite;
    }
}
// @codeCoverageIgnoreEnd