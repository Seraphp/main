<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
require_once 'PHPUnit/Framework.php';
require_once 'Exceptions/ExceptionHandlerTest.php';
/**
 * Class documentation
 */
class Exceptions_AllTests{

    public static function suite()
    {
        // @codeCoverageIgnoreStart
        $suite = new PHPUnit_Framework_TestSuite('Seraphp Exceptions');
        $suite->addTestSuite('ExceptionHandlerTest');
        return $suite;
        // @codeCoverageIgnoreEnd
    }
}