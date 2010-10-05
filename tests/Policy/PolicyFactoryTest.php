<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */

require_once 'Policy/PolicyFactory.class.php';
/**
 * Class documentation
 */
class PolicyFactoryTest extends PHPUnit_Framework_TestCase
{
    protected $_src;
    protected $_plugins;

    function setUp()
    {
        $this->_src = (object) array(
            'writer' => 'Douglas Adams',
            'numField' => 42,
            'title' => 'Hichhikers guide to the Galaxy'
        );
        $this->_plugins = array(
            'equal',
            'greater',
            'matches',
            'not_',
            'and_',
            'or_'
        );
    }

    function testFactoryPluginList()
    {
        $pF = \Seraphp\Policy\PolicyFactory::getInstance();
        $plugins = $pF->getPlugins();
        foreach ($this->_plugins as $plugin) {
            $this->assertContains($plugin, $plugins);
        }
    }

    function testFactoryEqual()
    {
        $pF = \Seraphp\Policy\PolicyFactory::getInstance();
        $spec = $pF->equal('writer', 'Douglas Adams');
        $this->assertTrue($spec->isSatisfiedBy($this->_src));
    }

    function testFactoryGreater()
    {
        $pF = \Seraphp\Policy\PolicyFactory::getInstance();
        $spec = $pF->greater('numField', '40');
        $this->assertTrue($spec->isSatisfiedBy($this->_src));
    }

    function testFactoryMatches()
    {
        $pF = \Seraphp\Policy\PolicyFactory::getInstance();
        $spec = $pF->matches('numField', '/^\d{2}$/');
        $this->assertTrue($spec->isSatisfiedBy($this->_src));
    }

    function testFactoryFacility()
    {
        $pF = \Seraphp\Policy\PolicyFactory::getInstance();
        $spec = $pF->equal('writer', 'Douglas Adams')->and_(
            $pF->matches('numField', '/^\d{2}$/')->and_(
                $pF->greater('numField', 40)
            )
        );
        $this->assertTrue($spec->isSatisfiedBy($this->_src));

        $spec = $pF->equal('title', 'Douglas Adams')->or_(
            $pF->matches('numField', '/^\d{2}$/')->and_(
                $pF->not_($pF->greater('numField', 40))
            )
        );
        $this->assertFalse($spec->isSatisfiedBy($this->_src));
    }

    function testCallInvalidPolicy()
    {
        $pF = \Seraphp\Policy\PolicyFactory::getInstance();
        $this->setExpectedException('\Seraphp\Exceptions\PluginException');
        $spec = $pF->less('numField', '');
    }

    function testGetInitialPluginsDir()
    {
        $pF = \Seraphp\Policy\PolicyFactory::getInstance();
        $this->assertEquals($pF->getPluginsDir(), getcwd().'/Policy');
    }

    function testInvalidPluginsDir()
    {
        $pF = \Seraphp\Policy\PolicyFactory::getInstance();
        $this->setExpectedException('\Seraphp\Exceptions\PluginException');
        $pF->setPluginsDir(getcwd().'/tests/Policy');
    }

    function testValidPluginsDir()
    {
        $pF = \Seraphp\Policy\PolicyFactory::getInstance();
        $pF->setPluginsDir('./Policy');
    }
}