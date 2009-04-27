<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
//require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Extensions/OutputTestCase.php';
require_once 'Exceptions/ExceptionHandler.class.php';
/**
 * Class documentation
 */
class ExceptionHandlerTest extends PHPUnit_Extensions_OutputTestCase{
    protected $exception, $output;

    function setUp()
    {
        $this->output = "Uncaught Exception, code: 0\nMessage: Test Exception\n";
    }

    function testPrintException()
    {
        $this->expectOutputString($this->output);
        ExceptionHandler::printException(new Exception('Test Exception'));
    }

    function testHandleException()
    {
        $this->expectOutputString($this->output);
        ExceptionHandler::handleException(new Exception('Test Exception'));
    }
}