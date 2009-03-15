<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
require_once 'PHPUnit/Framework.php';
require_once 'Policy/PolicyFactory.class.php';
/**
 * Class documentation
 */
class PolicyFactoryTest extends PHPUnit_Framework_TestCase{
    protected $src, $plugins;

    function setUp()
    {
        $this->src = (object) array(
            'writer' => 'Douglas Adams',
            'numField' => 42,
            'title' => 'Hichhikers guide to the Galaxy'
        );
        $this->plugins = array(
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
        $PF = PolicyFactory::getInstance();
        $plugins = $PF->getPlugins();
        foreach($this->plugins as $plugin){
            $this->assertContains($plugin,$plugins);
        }
    }

    function testFactoryEqual(){
        $PF = PolicyFactory::getInstance();
        $spec = $PF->equal('writer','Douglas Adams');
        $this->assertTrue($spec->isSatisfiedBy($this->src));
    }

    function testFactoryGreater(){
        $PF = PolicyFactory::getInstance();
        $spec = $PF->greater('numField','40');
        $this->assertTrue($spec->isSatisfiedBy($this->src));
    }

    function testFactoryMatches(){
        $PF = PolicyFactory::getInstance();
        $spec = $PF->matches('numField','/^\d{2}$/');
        $this->assertTrue($spec->isSatisfiedBy($this->src));
    }

    function testFactoryFacility()
    {
        $PF = PolicyFactory::getInstance();
        $spec = $PF->equal('writer','Douglas Adams')->and_(
            $PF->matches('numField','/^\d{2}$/')->and_(
            $PF->not_(
                $PF->greater('numfield', 40)
            )
        ));
        $this->assertTrue($spec->isSatisfiedBy($this->src));
        $spec = $PF->equal('title','Douglas Adams')->or_(
            $PF->matches('numField','/^\d{2}$/')->not_(
                $PF->greater('numfield', 40)
            )
        );
        $this->assertTrue($spec->isSatisfiedBy($this->src));
    }

    function testCallInvalidPolicy()
    {
        $PF = PolicyFactory::getInstance();
        $this->setExpectedException('PluginException');
        $spec = $PF->less('numField','');
    }

    function testGetInitialPluginsDir()
    {
        $PF = PolicyFactory::getInstance();
        $this->assertEquals($PF->getPluginsDir(),getcwd().'/Policy');
    }

    function testInvalidPluginsDir()
    {
        $PF = PolicyFactory::getInstance();
        $this->setExpectedException('PluginException');
        $PF->setPluginsDir(getcwd().'/Polic');
    }

    function testValidPluginsDir()
    {
        $PF = PolicyFactory::getInstance();
        $PF->setPluginsDir('./Policy');
    }
}
?>