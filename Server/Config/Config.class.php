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
    private static $_namespaces = array();

    public function xsearch($xpath, Config $node = null)
    {
        $node = ($node === null)?$this:$node;
        self::$_namespaces = $this->getNamespaces(true);
        //Register them with their prefixes
        foreach (self::$_namespaces as $prefix => $ns) {
            if ( empty($prefix) ) {
                $prefix = 'srph';
            }
            $res = $node->registerXPathNamespace($prefix, $ns);
        }
        return $node->xpath($xpath);
    }
}