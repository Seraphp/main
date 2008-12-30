<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
require_once 'PHPUnit/Framework.php';
require_once 'tests/Server/Config/ConfigTest.php';
/**
 * Class documentation
 */
class Config_AllTests{

    public static function suite()
    {
        // @codeCoverageIgnoreStart
        $suite = new PHPUnit_Framework_TestSuite('Config facilities');
        $suite->addTestSuite('ConfigTest');
        return $suite;
        // @codeCoverageIgnoreEnd
    }
}
?>