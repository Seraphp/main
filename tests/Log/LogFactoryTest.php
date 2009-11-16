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
class LogFactoryTest extends PHPUnit_Framework_TestCase
{
    protected $_xml = array();

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
        $this->_xml['standard'] = new Config($xmlStr);
    }

    function testGetInstanceWithConfPear()
    {
        $log = LogFactory::getInstance($this->_xml['standard'], 'PEAR');
        $this->assertThat($log, $this->isInstanceOf('Log'));
        $this->assertTrue($log->isComposite());
    }

    function testGetInstanceWithConfZend()
    {
        $log = LogFactory::getInstance($this->_xml['standard'], 'Zend');
        $this->assertThat($log, $this->isInstanceOf('Zend_Log'));
    }

    function testGetInstanceWithConfAuto()
    {
        $log = LogFactory::getInstance($this->_xml['standard']);
        $this->assertThat($log, $this->isInstanceOf('Zend_Log'));
    }

    function testGetInstanceWithNoSuchPackage()
    {
        $log = LogFactory::getInstance($this->_xml['standard'], 'Something');
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
        $logZ = LogFactory::getInstance();
        $this->assertThat($logZ, $this->isInstanceOf('Zend_Log'));
        $logS = LogFactory::getInstance($this->_xml['standard']);
        $this->assertThat($logS, $this->isInstanceOf('Zend_Log'));
    }

    function testMissingHandlerInConf()
    {
        $this->_xml['wrong'] = clone $this->_xml['standard'];
        unset($this->_xml['wrong']->logs->log[1]['handler']);
        $this->setExpectedException('LogException');
        $log = LogFactory::getInstance($this->_xml['wrong']);
    }

    function testMailHandler()
    {
        $this->_xml['mail'] = clone $this->_xml['standard'];
        $this->_xml['mail']->logs->log[0]['handler'] = 'Mail';
        $this->_xml['mail']->logs->log[0]->conf['from'] =
        'antronin+test@gmail.com';
        $this->_xml['mail']->logs->log[0]->conf['to'] = 'antronin@gmail.com';
        $this->_xml['mail']->logs->log[0]->conf['subject'] = 'Log mail';
        $this->_xml['mail']->logs->log[0]->conf['layout'] = 'Entry: %s';
        $log = LogFactory::getInstance($this->_xml['mail']);
        $this->assertThat($log, $this->isInstanceOf('Zend_Log'));
    }

    function testSyslogHandler()
    {
        $this->_xml['syslog'] = clone $this->_xml['standard'];
        $this->_xml['syslog']->logs->log[0]['handler'] = 'Syslog';
        $this->_xml['syslog']->logs->log[0]->conf['application'] = 'Searphp';
        $this->_xml['syslog']->logs->log[0]->conf['facility'] = 'LOG_DAEMON';
        $log = LogFactory::getInstance($this->_xml['syslog']);
        $this->assertThat($log, $this->isInstanceOf('Zend_Log'));
    }
    function testWithEmptyConf()
    {
        $log = LogFactory::getInstance(new Config('<test/>'));
        $this->assertThat($log, $this->isInstanceOf('Zend_Log'));
    }
}