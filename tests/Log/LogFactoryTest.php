<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
require_once 'PHPUnit/Framework.php';
require_once 'Log/LogFactory.class.php';
require_once 'Server/Config/Config.class.php';
/**
 * Class documentation
 */
class LogFactoryTest extends PHPUnit_Framework_TestCase{
    protected $xml;

    function setUp()
    {
        $xmlStr = <<<XML
     <server>
        <logs>
            <log handler="console" name="" ident="Seraphp" level="ERR">
                <conf stream="STDOUT" buffering="false" />
            </log>
            <log handler="file" name="out.log" ident="DEBUG" level="DEBUG">
                <conf />
            </log>
            <log handler="file" name="out.log" ident="TEST2" level="INFO">
                <conf />
            </log>
        </logs>
    </server>
XML;
        $this->xml = new Config($xmlStr);
    }

    function testGetInstanceWithConfPear()
    {
        $log = LogFactory::getInstance($this->xml, 'PEAR');
        $this->assertThat($log, $this->isInstanceOf('Log'));
        $this->assertTrue($log->isComposite());
    }

    function testGetInstanceWithConfZend()
    {
        $log = LogFactory::getInstance($this->xml, 'Zend');
        $this->assertThat($log, $this->isInstanceOf('Zend_Log'));
    }

    function testGetInstanceWithConfAuto()
    {
        $log = LogFactory::getInstance($this->xml);
        $this->assertThat($log, $this->isInstanceOf('Zend_Log'));
    }

    function testGetInstanceNoConfPear()
    {
        $log = LogFactory::getInstance(null, 'PEAR');
        $this->assertThat($log, $this->isInstanceOf('Log'));
        $this->assertTrue($log->isComposite());
    }

    function testGetInstanceNoConfZend()
    {
        $log = LogFactory::getInstance(null, 'Zend');
        $this->assertThat($log, $this->isInstanceOf('Zend_Log'));
    }

    function testGetInstanceNoConfAuto()
    {
        $log = LogFactory::getInstance();
        $this->assertThat($log, $this->isInstanceOf('Zend_Log'));
    }

    function testInstantiateDifferently()
    {
        $log1 = LogFactory::getInstance();
        $log2 = LogFactory::getInstance($this->xml);
    }
}
