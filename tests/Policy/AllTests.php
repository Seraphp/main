<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
require_once 'PHPUnit/Framework.php';
require_once 'Policy/SpecificationTest.php';
require_once 'Policy/PolicyFactoryTest.php';
/**
 * Class documentation
 */
class Policy_AllTests{

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