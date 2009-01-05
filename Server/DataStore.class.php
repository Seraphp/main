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
     * Overwright flag for existing data writing in the registry
     *
     * @var boolean
     */
    protected $overwrite = true;

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
     * If the variable is already registered, it will be overwritten by default.
     * To changes this behaviour, call "isOverwrite()" with false.
     *
     * @param string  $key
     * @param mixed  $value
     * @return boolean
     */
    public function __set($key, $value)
    {
        if($this->overwrigth === true)
        {
            $this->store[$key]=$value;
        }
        else
        {
            if($this->isExists($key) === true)
            {
                 return false;
            }
            else
            {
                $this->store[$key]=$value;
            }
        }
        return true;
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

    /**
     * Sets overwrite behaviour when storing a value
     * which already exists in registry. Default is true
     *
     * @param boolean $flag
     */
    public function isOverwrite($flag)
    {
        $this->overwrite = (boolean) $flag;
    }
}
?>