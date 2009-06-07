<?php
/**
 * Holds ConfigFactory class implementatio
 *
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
class ConfigFactory implements Singleton
{
    private static $_log;
    private static $_instance = null;
    private $_registry = null;
    private $_configPath = '';
    private $_configFile = '';
    private $_xml = null;
    private $_namespaces = array();

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

    /**
     * @param string $name  ID of configuration part to load
     * @return Config
     */
    public function getConf($name)
    {
        self::$_log->debug(__METHOD__.' called');
        self::$_log->debug('Getting "'.$name. '" config');
        if ($this->_registry->$name === null) {
            $this->_registry->$name = $this->_parse($name);
        }
        return $this->_registry->$name;
    }

    /* (non-PHPdoc)
     * @see Singleton#getInstance()
     */
    public function getInstance()
    {
        self::$_log = LogFactory::getInstance();
        self::$_log->debug(__METHOD__.' called');
        if (self::$_instance === null) {
            self::$_instance = new self(Registry::getInstance());
        }
        return self::$_instance;
    }

    /**
     * @param string $xmlFile  The name of the file to load
     * @return void
     */
    private function _load($xmlFile='')
    {
        self::$_log->debug(__METHOD__.' called');
        self::$_log->debug('Loading "'.$xmlFile. '"');
        if (!empty($xmlFile)) {
            $this->setXmlSrc($xmlFile);
        }
        $this->_xml = simplexml_load_file($this->_configPath.
                                            DIRECTORY_SEPARATOR.
                                            $this->_configFile,
                                            'Config',
                                            LIBXML_COMPACT|LIBXML_NOBLANKS);
        //Fetch all namespaces
        $this->_namespaces = $this->_xml->getNamespaces(true);
    }

    /**
     * @param string $xpath
     * @param SimpleXMLElement $node
     * @return SimpleXMLElement
     */
    public function xsearch($xpath, $node = null)
    {
        self::$_log->debug(__METHOD__.' called');
        self::$_log->debug('Xpath: "'.$xpath. '"');
        $node = ($node === null)?$this->_xml:$node;
        //Register them with their prefixes
        foreach ($this->_namespaces as $prefix => $ns) {
            if ( empty($prefix) ) {
                $prefix = 'srph';
            }
            $res = $node->registerXPathNamespace($prefix, $ns);
        }
        return $node->xpath($xpath);
    }

    /**
     * @param string $xmlFile  Full path of xml file to load
     * @return void
     */
    public function setXmlSrc($xmlFile)
    {
        if (is_file($xmlFile)) {
            $this->_configPath = dirname($xmlFile);
            $this->_configFile = basename($xmlFile);
        } else throw new Exception("$xmlFile cannot be loaded!");
    }

    /**
     * @param string $name  Server configuration id
     * @return SimpleXMLElement
     */
    private function _parse($name)
    {
        self::$_log->debug(__METHOD__.' called');
        if ($this->_xml === null) {
            $this->_load();
        }
        self::$_log->debug('Searching for node: "//srph:servers/srph:server'.
        '[@id='.$name. ']"');
        $serverConfXML = $this->xsearch('//srph:servers/srph:server[@id="'
            .$name.'"]');
        if ($serverConfXML === false) {
            throw new ConfigException('Failed to parse the confg file: '
            .$this->_configFile. ' from '.$this->_configPath);
        }
        switch (count($serverConfXML)) {
            case 0:
                throw new ConfigException(
                    sprintf("Server ID: '%s' not exists in config file %s!",
                    $name,
                    $this->_configPath.DIRECTORY_SEPARATOR.$this->_configFile));
                break;
            case 1:
                $parentNode = $this->xsearch('..', $serverConfXML[0]);
                /*$props = array_keys((array)$serverConfXML[0]->children());
                $parentNode = $this->xsearch('..', $serverConfXML[0]);
                $conf = new Config;
                $conf->name = (string) $serverConfXML[0]->attributes()->id;
                $conf->pidpath = (string) $parentNode[0]->attributes()->pidpath;
                if (in_array('urimap', $props)) {
                  $conf->urimap = (array) $serverConfXML[0]->urimap->children();
                }
                if (in_array('instance', $props)) {
                    $conf->instance =
                        (array) $serverConfXML[0]->instance->children();
                }
                if (in_array('resources', $props)) {
                    $conf->includes =
                     (array) $serverConfXML[0]->resources->includes->children();
                }*/
                $serverConfXML[0]->addAttribute('pidpath',
                    $parentNode[0]->attributes()->pidpath);
                $conf = $serverConfXML[0];
                break;
            default:
                throw new ConfigException(
                    sprintf("Server ID: '%s' exists several ".
                        "times in config file %s!",
                        $name,
                        $this->_configPath.DIRECTORY_SEPARATOR.
                        $this->_configFile));
                break;
            }
        return $conf;
    }
}