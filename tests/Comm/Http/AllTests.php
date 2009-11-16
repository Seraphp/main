<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
// @codeCoverageIgnoreStart
require_once 'PHPUnit/Framework.php';
require_once 'HttpFactoryTest.php';
require_once 'HttpCookieTest.php';
require_once 'HttpRequestTest.php';
require_once 'HttpResponseTest.php';
/**
 * Class documentation
 */
class Http_AllTests
{

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Http facilities');
        $suite->addTestSuite('HttpCookieTest');
        $suite->addTestSuite('HttpRequestTest');
        $suite->addTestSuite('HttpResponseTest');
        $suite->addTestSuite('HttpFactoryTest');
        return $suite;
    }
}
// @codeCoverageIgnoreEnd