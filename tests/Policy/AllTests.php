<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
require_once 'PHPUnit/Framework.php';
require_once 'tests/Policy/SpecificationTest.php';
require_once 'tests/Policy/PolicyFactoryTest.php';
/**
 * Class documentation
 */
class Policy_AllTests{

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Phaser Policy');
        $suite->addTestSuite('PolicyFactoryTest');
        $suite->addTestSuite('SpecificationTest');
        return $suite;
    }
}
?>