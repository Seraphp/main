<?php
/**
 * Holds implememntation of Configuration class
 * 
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @package Server
 * @subpackage Config
 * @filesource
 */
//namespace Phaser::Server::Config;
/**
 * Config class decoates DataStore
 *
 * The class hold the configuration of a server and can signal
 * if it is changed.
 *  
 * @package Server
 * @subpackage Config
 */
class Config extends DataStore{
    
    private $dirty = false;
 
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
        $this->dirty = true;
    }
     
    private function isChanged()
    {
        return ($this->dirty);
    }
}
?>