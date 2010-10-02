<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
// @codeCoverageIgnoreStart

require_once 'IpcFactoryTest.php';
require_once 'IpcUnixsocketsTest.php';
/**
 * Class documentation
 */
class Ipc_AllTests
{

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Ipc facilities');
        $suite->addTestSuite('IpcFactoryTest');
        $suite->addTestSuite('IpcUnixsocketsTest');
        return $suite;
    }
}
// @codeCoverageIgnoreEnd