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
require_once 'Server/DataStore.class.php';
/**
 * Config class decoates DataStore
 *
 * The class hold the configuration of a server and can signal
 * if it is changed.
 *
 * @package Server
 * @subpackage Config
 */
class Config extends DataStore
{

    protected $_dirty = false;

    /**
     * Registers a value with a key in the registry.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set($key, $value)
    {
        parent::__set($key,$value);
        $this->_dirty = true;
    }

    public function isChanged()
    {
        return ($this->_dirty);
    }

    public function clearState()
    {
        $this->_dirty = false;
    }
}

class ConfigException extends Exception{}