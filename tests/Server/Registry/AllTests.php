<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
require_once 'PHPUnit/Framework.php';
require_once 'tests/Server/Registry/RegistryTest.php';
/**
 * Class documentation
 */
class Registry_AllTests{

    public static function suite()
    {
        // @codeCoverageIgnoreStart
        $suite = new PHPUnit_Framework_TestSuite('Registry facilities');
        $suite->addTestSuite('RegistryTest');
        return $suite;
        // @codeCoverageIgnoreEnd
    }
}
?>