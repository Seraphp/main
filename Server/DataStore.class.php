<?php
/**
 * Holds implememntation of DataStore class
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @copyright Copyright (c) 2008, Peter Nagy
 * @package Server
 * @filesource
 */
/***/
//namespace Phaser::Server;
/**
 * DataStore class for storing keys with values
 *
 * The class is capable of storing any kind of value with a key, in an obejct based modell
 *
 * @package Server
 */
class DataStore{

	/**
     * Stores key=>value pairs of registry entries
     *
     * @var array
     */
    protected $store = array();

    /**
     * Checks if a key is exists in the store.
     *
     * @param string $key
     * @return boolean
     */
    public function isExists($key)
    {
        return (array_key_exists($key, $this->store));
    }

    /**
     * Registers a value with a key in the registry.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set($key, $value)
    {
        if(is_array($this->store[$key]))
        {
            $this->store = array_merge_recursive($this->store[$key], $value);
        }
        else
        {
            $this->store[$key]=$value;
        }

    }

    /**
     * Returns a value registered on a key in the store.
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        if($this->isExists($key))
        {
            return $this->store[$key];
        }
        else return null;
    }
}
?>