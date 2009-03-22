<?php
/**
 * Holds ConfigFactory class implementation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @copyright Copyright (c) 2008, Peter Nagy
 * @package Server
 * @subpackage Config
 * @filesource
 */
/***/
//namespace Seraphp\Server\Config;
require_once 'Singleton.interface.php';
require_once 'Server/Config/Config.class.php';
require_once 'Server/Registry/Registry.class.php';
/**
 * Class for parsing xml file and loading the settings into
 * AppRegistry.
 *
 * @package Server
 * @subpackage Config
 */
class ConfigFactory implements Singleton{

    private static $_instance = null;
    private $_registry = null;
    private $_configPath = '';
    private $_configFile = '';
    private $_xml = null;

    private function __construct(Registry $reg)
    {
        $this->_registry = $reg;
        $this->_configPath = dirname(__FILE__);
        $this->_configFile = 'seraphpConf.xml';
    }

	/**
     * Disabled cloning facility to preserve only 1 instance
     * @throws Exception if used
     */
    public function __clone()
    {
        throw new Exception('Cloning of'. __CLASS__ .' is disabled!');
    }

    public function getConf($name)
    {
        if($this->_registry->$name === null)
        {
            $this->_registry->$name = $this->parse($name);
        }
        return $this->_registry->$name;
    }

    public function getInstance()
    {
        if(self::$_instance === null)
        {
            self::$_instance = new self(Registry::getInstance());
        }
        return self::$_instance;
    }

    private function load($xmlFile='')
    {
        if(!empty($xmlFile))
        {
            $this->setXmlSrc($xmlFile);
        }
        $this->_xml = simplexml_load_file($this->_configPath.DIRECTORY_SEPARATOR.$this->_configFile);
        //Fetch all namespaces
        $namespaces = $this->_xml->getNamespaces(true);
        //Register them with their prefixes
        foreach ($namespaces as $prefix => $ns)
        {
            $this->_xml->registerXPathNamespace($prefix, $ns);
        }
    }

    public function setXmlSrc($xmlFile)
    {
        if(is_file($xmlFile))
        {
            $this->_configPath = dirname($xmlFile);
            $this->_configFile = basename($xmlFile);
        }
        else throw new Exception("$xmlFile cannot be loaded!");
    }

    private function parse($name)
    {
        if($this->_xml === null)
        {
            $this->load();
        }
        $serverConfXML = $this->_xml->xpath('//servers/server[@id="'.$name.'"]');
        switch (count($serverConfXML))
        {
            case 0:
                throw new ConfigException(sprintf("Server ID: '%s' not exists in config file %s!",$name,$this->_configPath.DIRECTORY_SEPARATOR.$this->_configFile));
            break;
            case 1:
                $props = array_keys((array)$serverConfXML[0]->children());
                $parentNode = $serverConfXML[0]->xpath('..');
                $conf = new Config;
                $conf->name = (string) $serverConfXML[0]->attributes()->id;
                $conf->pidpath = (string) $parentNode[0]->attributes()->pidpath;
                if(in_array('urimap',$props))
                {
                    $conf->urimap = (array) $serverConfXML[0]->urimap->children();
                }
                if(in_array('instance',$props))
                {
                    $conf->instance = (array) $serverConfXML[0]->instance->children();
                }
                if(in_array('resources',$props))
                {
                    $conf->includes = (array) $serverConfXML[0]->resources->includes->children();
                }
            break;
            default:
                throw new ConfigException(sprintf("Server ID: '%s' exists several times in config file %s!",$name,$this->_configPath.DIRECTORY_SEPARATOR.$this->_configFile));
        }
        return $conf;
    }
}
