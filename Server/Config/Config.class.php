<?php
/**
 * Holds implememntation of Configuration class
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
/**
 * Config class decorates SimpleXMLElement
 *
 * The class hold the configuration of a server and can signal
 * if it is changed.
 *
 * @package Server
 * @subpackage Config
 */
class Config extends SimpleXMLElement
{
    private $_namespaces = array();
    protected static $_dirty = false;

    public function isChanged()
    {
        return self::$_dirty;
    }

    public function clearState()
    {
        self::$_dirty = false;
    }

    public function xsearch($xpath, Config $node = null)
    {
        $node = ($node === null)?$this:$node;
        $this->_namespaces = $this->getNamespaces(true);
        //Register them with their prefixes
        foreach ($this->_namespaces as $prefix => $ns) {
            if ( empty($prefix) ) {
                $prefix = 'srph';
            }
            $res = $node->registerXPathNamespace($prefix, $ns);
        }
        return $node->xpath($xpath);
    }
}