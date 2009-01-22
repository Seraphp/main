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
     * Enter description here...
     *
     * @var boolean
     */
    protected $dirty = false;

    /**
     * Overwright flag for existing data writing in the registry
     *
     * @var boolean
     */
    protected $overwrite = true;

    /**
     * Checks if a key is exists in the store.
     *
     * Magic method: called when asking isset() on a key.
     *
     * @param string $key
     * @return boolean
     * @since PHP 5.1.0
     */
    public function __isset($key)
    {
        return (array_key_exists($key, $this->store));
    }

    /**
     * Unset a key is it exists.
     *
     * Magic method: called when fireing unset on a key.
     *
     * @param string $key
     * @return void
     * @since PHP 5.1.0
     */
    public function __unset($key)
    {
        if(isset($this->$key))
        {
            unset($this->store[$key]);
        }
    }

    /**
     * Registers a value with a key in the registry.
     *
     * If the variable is already registered, it will be overwritten by default.
     * To changes this behaviour, call "setOverwrite()" with false.
     *
     * @param string  $key
     * @param mixed  $value
     * @return boolean
     */
    public function __set($key, $value)
    {
        if($this->overwrite === true)
        {
            $this->store[$key]=$value;
        }
        else
        {
            if(isset($this->$key) === true)
            {
                 return false;
            }
            else
            {
                $this->store[$key]=$value;
            }
        }
        $this->dirty = true;
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
        if(isset($this->$key))
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
    public function setOverwrite($flag)
    {
        $this->overwrite = (boolean) $flag;
    }
}
?>