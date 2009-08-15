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
    protected $xml = array();

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
        $this->xml['standard'] = new Config($xmlStr);
    }

    function testGetInstanceWithConfPear()
    {
        $log = LogFactory::getInstance($this->xml['standard'], 'PEAR');
        $this->assertThat($log, $this->isInstanceOf('Log'));
        $this->assertTrue($log->isComposite());
    }

    function testGetInstanceWithConfZend()
    {
        $log = LogFactory::getInstance($this->xml['standard'], 'Zend');
        $this->assertThat($log, $this->isInstanceOf('Zend_Log'));
    }

    function testGetInstanceWithConfAuto()
    {
        $log = LogFactory::getInstance($this->xml['standard']);
        $this->assertThat($log, $this->isInstanceOf('Zend_Log'));
    }

    function testGetInstanceWithNoSuchPackage()
    {
        $log = LogFactory::getInstance($this->xml['standard'], 'Something');
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
        $this->assertThat($log1, $this->isInstanceOf('Zend_Log'));
        $log2 = LogFactory::getInstance($this->xml['standard']);
        $this->assertThat($log2, $this->isInstanceOf('Zend_Log'));
    }

    function testMissingHandlerInConf()
    {
        $this->xml['wrong'] = clone $this->xml['standard'];
        unset($this->xml['wrong']->logs->log[1]['handler']);
        $this->setExpectedException('LogException');
        $log = LogFactory::getInstance($this->xml['wrong']);
    }

    function testMailHandler()
    {
        $this->xml['mail'] = clone $this->xml['standard'];
        $this->xml['mail']->logs->log[0]['handler'] = 'Mail';
        $this->xml['mail']->logs->log[0]->conf['from'] = 'antronin+test@gmail.com';
        $this->xml['mail']->logs->log[0]->conf['to'] = 'antronin@gmail.com';
        $this->xml['mail']->logs->log[0]->conf['subject'] = 'Log mail';
        $this->xml['mail']->logs->log[0]->conf['layout'] = 'Entry: %s';
        $log = LogFactory::getInstance($this->xml['mail']);
        $this->assertThat($log, $this->isInstanceOf('Zend_Log'));
    }

    function testSyslogHandler()
    {
        $this->xml['syslog'] = clone $this->xml['standard'];
        $this->xml['syslog']->logs->log[0]['handler'] = 'Syslog';
        $this->xml['syslog']->logs->log[0]->conf['application'] = 'Searphp';
        $this->xml['syslog']->logs->log[0]->conf['facility'] = 'LOG_DAEMON';
        $log = LogFactory::getInstance($this->xml['syslog']);
        $this->assertThat($log, $this->isInstanceOf('Zend_Log'));
    }
}