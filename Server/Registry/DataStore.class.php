<?php
/**
 * Holds implememntation of DataStore class
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @copyright Copyright (c) 2008, Peter Nagy
 * @package Server
 * @subpackage Registry
 * @filesource
 */
/***/
namespace Seraphp\Server\Registry;
/**
 * DataStore class for storing keys with values
 *
 * The class is capable of storing any kind of value with a key, in an object
 * based modell
 *
 * @package Server
 * @subpackage Registry
 */
class DataStore
{

    /**
     * Stores key=>value pairs of registry entries
     *
     * @var array
     */
    protected $_store = array();

    /**
     * @var StoreEngine
     */
    protected $_engine = null;

    /**
     * Overwrite flag for existing data writing in the registry
     *
     * @var boolean
     */
    protected $_overwrite = true;

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
        return (array_key_exists($key, $this->_store));
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
        if (isset($this->$key)) {
            unset($this->_store[$key]);
            if (isset($this->_engine)) {
                $this->_engine->save($this->_store);
            }
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
     * @return void
     */
    public function __set($key, $value)
    {
        if (strpos($key, '_') === 0) {
            return;
        }
        if ($this->_overwrite === true) {
            $this->_store[$key]=$value;
            if (isset($this->_engine)) {
                $this->_engine->save($this->_store);
            }
        } else {
            if (isset($this->$key) === true) {
                 return;
            } else {
                $this->_store[$key]=$value;
                if (isset($this->_engine)) {
                    $this->_engine->save($this->_store);
                }
            }
        }
        return;
    }

    /**
     * Returns a value registered on a key in the store.
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        if (isset($this->$key) &&
            strpos($key, '_') !== 0) {
            return $this->_store[$key];
        } else return null;
    }

    /**
     * Sets overwrite behaviour when storing a value
     * which already exists in registry. Default is true
     *
     * @param boolean $flag
     * @return boolen  The actual value of overwrite
     */
    public function setOverwrite($flag)
    {
        $this->_overwrite = (boolean) $flag;
        return $flag;
    }

    public function setEngine(StoreEngine $engine, $savePath = null)
    {
        $this->_engine = $engine;
        $this->_engine->setUp($savePath);
        $this->_store = (array) $this->_engine->load();
    }

    public function getEngineType()
    {
        return get_class($this->_engine);
    }
}