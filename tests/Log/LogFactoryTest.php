<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
require_once 'PHPUnit/Framework.php';
require_once 'Log/LogFactory.class.php';
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
            <log handler="console" name="" ident="Seraphp" level="PEAR_LOG_ERR">
                <conf stream="STDOUT" buffering="false" />
            </log>
            <log handler="file" name="out.log" ident="DEBUG" level="PEAR_LOG_ALL">
                <conf />
            </log>
        </logs>
    </server>
XML;
        $this->xml = new Config($xmlStr);
    }

    function testGetInstanceWithConf()
    {
        $log = LogFactory::getInstance($this->xml);
        $this->assertThat($log, $this->isInstanceOf('Log'));
        $this->assertTrue($log->isComposite());
    }

    function testGetInstanceNoConf()
    {
        $log = LogFactory::getInstance();
        $this->assertThat($log, $this->isInstanceOf('Log'));
        $this->assertTrue($log->isComposite());
    }
}
