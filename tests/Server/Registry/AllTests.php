<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
// @codeCoverageIgnoreStart
require_once 'PHPUnit/Framework.php';
require_once 'RegistryTest.php';
require_once 'DataStoreTest.php';
require_once 'AppServerRegistryTest.php';
/**
 * Class documentation
 */
class Registry_AllTests{

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Registry facilities');
        $suite->addTestSuite('DataStoreTest');
        $suite->addTestSuite('RegistryTest');
        $suite->addTestSuite('AppServerRegistryTest');
        return $suite;

    }
}// @codeCoverageIgnoreEnd