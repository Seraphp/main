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

    function testSetupRecall()
    {
        $this->assertNull(ExceptionHandler::setup());
        $this->assertTrue(ExceptionHandler::recall());
    }

    function testHandling()
    {
        ExceptionHandler::setup();
        $this->setExpectedException('Exception', 'Test Exception');
        throw new Exception('Test Exception');
    }
}