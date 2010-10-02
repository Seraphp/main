<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
// @codeCoverageIgnoreStart

require_once 'JsonRpcRequestTest.php';
require_once 'JsonRpcResponseTest.php';
require_once 'JsonRpcProxyTest.php';
/**
 * Class documentation
 */
class JsonRpc_AllTests
{

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('JsonRpc facilities');
        $suite->addTestSuite('JsonRpcRequestTest');
        $suite->addTestSuite('JsonRpcResponseTest');
        $suite->addTestSuite('JsonRpcProxyTest');
        return $suite;
    }
}
// @codeCoverageIgnoreEnd