<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */

require_once 'SpecificationTest.php';
require_once 'PolicyFactoryTest.php';
/**
 * Class documentation
 */
class Policy_AllTests
{

    public static function suite()
    {
        // @codeCoverageIgnoreStart
        $suite = new PHPUnit_Framework_TestSuite('Seraphp Policy');
        $suite->addTestSuite('PolicyFactoryTest');
        $suite->addTestSuite('SpecificationTest');
        return $suite;
        // @codeCoverageIgnoreEnd
    }
}