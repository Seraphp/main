<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */

require_once 'Exceptions/ExceptionHandler.class.php';
/**
 * Class documentation
 */
class ExceptionHandlerTest extends PHPUnit_Framework_TestCase
{

    function testSetupRecall()
    {
        $this->assertNull(\Seraphp\Exceptions\ExceptionHandler::setup());
        $this->assertTrue(\Seraphp\Exceptions\ExceptionHandler::recall());
    }

    function testHandling()
    {
        \Seraphp\Exceptions\ExceptionHandler::setup();
        $this->setExpectedException('\Exception', 'Test Exception');
        throw new \Exception('Test Exception');
    }
}