<?php
/**
 * Holds implementation of singleton Registry class
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
/**
 * Register class with nested keys
 *
 * Singleton implementation of the Registry pattern for storing and
 * retrieving values with keys.
 *
 * @package Phaser
 * @subpackage Registry
 */
class Registry{

    /**
     * Holds self reference
     *
     * @var Registry
     */
    static private $instance = null;
    
	/**
     * Stores key=>value pairs of registry entries
     *
     * @var array
     */
    private $store = array();

    /**
     * Disabled constructor
     *
     */
    private function __construct()
    {}
    
	/**
     * Disabled cloning facility to preserve only 1 instance
     * @throws Exception  if used
     */
    public function __clone()
    {
        throw new Exception();
    }

    /**
     * Returns instance of class
     *
     * @return unknown
     */
    public function getInstance()
    {
        if(self::$instance === null){
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Checks recursively if a key is exists in the store.
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