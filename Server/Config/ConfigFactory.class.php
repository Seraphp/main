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
//namespace Phaser::Server::Config;
require_once 'Singleton.interface.php';
require_once 'Server/Config/Config.class.php';
require_once 'Server/Registry/AppServerRegistry.class.php';
/**
 * Class for parsing xml file and loading the settings into
 * AppRegistry.
 *
 * @package Server
 * @subpackage Config
 */
class ConfigFactory implements Singleton{

    private static $instance = null;
    private static $registry = null;
    private $configPath = '';
    private $configFile = '';
    private $xml = null;

    private function __construct(AppServerRegistry $reg)
    {
        self::$registry = $reg;
        $this->configPath = dirname(__FILE__);
        $this->configFile = 'phaserConf.xml';
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
        if(self::$registry->$name === null)
        {
            self::$registry->$name = $this->parse($name);
        }
        return self::$registry->$name;
    }

    public function getInstance()
    {
        if(self::$instance === null)
        {
            self::$instance = new self(AppServerRegistry::getInstance());
        }
        return self::$instance;
    }

    private function load($xmlFile='')
    {
        if(!empty($xmlFile))
        {
            $this->setXmlSrc($xmlFile);
        }
        $this->xml = simplexml_load_file($this->configPath.DIRECTORY_SEPARATOR.$this->configFile);
    }

    public function setXmlSrc($xmlFile)
    {
        if(is_file($xmlFile))
        {
            $this->configPath = dirname($xmlFile);
            $this->configFile = basename($xmlFile);
        }
        else throw new Exception("$xmlFile cannot be loaded!");
    }

    private function parse($name)
    {
        if($this->xml === null)
        {
            self::load();
        }
        $serverConfXML = $this->xml->xpath("//servers/server[@id='$name']");
        switch (count($serverConfXML))
        {
            case 0:
                throw new ConfigException(sprintf("Server ID: '%s' not exists in config file %s!",$name,$this->configPath.DIRECTORY_SEPARATOR.$this->configFile));
            break;
            case 1:
                $props = array_keys((array)$serverConfXML[0]->children());
                $parentNode = $serverConfXML[0]->xpath('..');
                $conf = new Config;
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
                throw new ConfigException(sprintf("Server ID: '%s' exists several times in config file %s!",$name,$this->configPath.DIRECTORY_SEPARATOR.$this->configFile));
        }
        return $conf;
    }
}
?>