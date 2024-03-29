<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */

require_once 'ExceptionHandlerTest.php';
/**
 * Class documentation
 */
class Exceptions_AllTests
{

    public static function suite()
    {
        // @codeCoverageIgnoreStart
        $suite = new PHPUnit_Framework_TestSuite('Seraphp Exceptions');
        $suite->addTestSuite('ExceptionHandlerTest');
        return $suite;
        // @codeCoverageIgnoreEnd
    }
}