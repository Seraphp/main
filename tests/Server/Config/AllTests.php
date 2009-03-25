<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
require_once 'PHPUnit/Framework.php';
require_once 'tests/Server/Config/ConfigXmlValidityTest.php';
require_once 'tests/Server/Config/ConfigTest.php';
require_once 'tests/Server/Config/ConfigFactoryTest.php';
/**
 * Class documentation
 * // @codeCoverageIgnoreStart
 */
class Config_AllTests{

    public static function suite()
    {

        $suite = new PHPUnit_Framework_TestSuite('Config facilities');
        $suite->addTestSuite('ConfigXmlValidityTest');
        $suite->addTestSuite('ConfigTest');
        $suite->addTestSuite('ConfigFactoryTest');
        return $suite;
    }
}
// @codeCoverageIgnoreEnd