<?php
/**
 * Contains implementation of PolicyFactory and related exceptions
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @copyright Copyright (c) 2008, Peter Nagy
 * @package Policy
 * @filesource
 */
/***/
//namespace Seraphp\Policy;
require_once 'Policy/Specification.interface.php';
require_once 'Exceptions/PluginException.class.php';
/**
 * Factory class for creating PolicyRules
 *
 * Registers all the available Specification classes as its own functions.
 * This way you can create more complex policy rules.
 * Should be static class but for this it will need PHP 5.3
 *
 * @package Policy
 * @todo: Refactor class to make it static using PHP 5.3 __staticCall feature
 */
class PolicyFactory
{
    /**
     * Stores usable plugins
     *
     * @var array
     */
    private $_plugins = array();
    /**
     * Stores directory path to search for policy plugins
     *
     * @var string
     */
    private $_pluginsDir = '';
    /**
     * Stores selfreference for Singleton pattern implementation
     *
     * @var PolicyFactory
     */
    private static $_instance = null;
    /**
     * Private constructor to force singleton/static usage
     *
     * Any call to constructor will result reading in plugins from
     * default directory, which is the same dir as this file is located.
     */
    private function __construct()
    {
        $this->_readPlugins();
    }

    /**
     * Returns Singleton instance
     *
     * @return PolicyFactory
     */
    public function getInstance()
    {
        if ( self::$_instance == null ) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    /**
     * Magic method to redirect methd calls to certain policy classes
     *
     * @param string $func
     * @param array $params
     * @return Specification
     */
    public function __call($func, $params)
    {
        $this->_readPlugins();
        if ( array_key_exists($func, $this->_plugins) ) {
            $class = new ReflectionClass( $this->_plugins[$func] );
            return ( $class->newInstanceArgs($params) );
        }
        else throw new PluginException('No plugin mapped to function '.$func);
    }

    /**
     * Read plugins from directory
     *
     */
    private function _readPlugins()
    {
        if ($this->_plugins === array()) {
            if ($this->_pluginsDir === '') {
                $this->_pluginsDir = dirname(__FILE__);
            } else {
                $this->_pluginsDir = $this->_pluginsDir;
            }
            $d = dir($this->_pluginsDir);
            while ( false !== ( $entry = $d->read() ) ) {
                if ($entry != '.' && $entry != '..') {
                    $this->_registerPlugin($entry);
                }
            }
            $d->close();
        }

    }

    /**
     * Registers policy implementation classes as callable functions
     *
     * Classes has to be called <PolicyName>Specification.class.php to make PolicyFactory
     * able to register them. If <PolicyName> does not start with "Field", the methodname
     * will be postfixed with "_" to avoid name clashes with existing PHP native function.
     * For example:
     *   AndSpecification.class.php will be registered as "PolicyFactory::and_".
     *
     * @param string $plugin
     * @throws PolicyPluginException
     */
    private function _registerPlugin($plugin)
    {
        $nameMatch = preg_match('/^(.+)Specification.class.php$/',
                                $plugin,
                                $matches);
        if ( $nameMatch !== FALSE && $nameMatch > 0) {
            require_once $this->_pluginsDir.'/'.$plugin;
            $className = substr($plugin, 0, -10);
            $class = new ReflectionClass($className);
            if ( $class->implementsInterface('Specification') ) {
                if ( strpos($matches[1], 'Field') === 0 ) {
                    $key = substr($matches[1], 5);
                } else {
                    $key = $matches[1].'_';
                }
                if ( !empty($key) ) {
                    $this->_plugins[strtolower($key)] =  $className;
                }
            } else {
                throw new PluginException(
                    'Specification interface not implemented in '.$plugin
                );
            }
        }
    }
    /**
     * Sets plugins directory
     *
     * Also deletes already registered plugins and reload the plugins list.
     * @todo: Refactor class to make it able to simply add a new directory to
     * the plugins' directory's lists.
     *
     * @param string $dir
     */
    public function setPluginsDir($dir)
    {
        $this->_pluginsDir = $dir;
        $this->_plugins = array();
        $this->_readPlugins();
    }

    /**
     * Returns plugin directory
     *
     * @return string
     */
    public function getPluginsDir()
    {
        return $this->_pluginsDir;
    }

    /**
     * Returns the list of usable method names for plugins
     *
     * @return array
     */
    public function getPlugins()
    {
        $this->_readPlugins();
        return ( array_keys($this->_plugins) );
    }
}