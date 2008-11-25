<?php
/**
 * Holds implementation of singleton Registry class
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @copyright Copyright (c) 2008, Peter Nagy
 * @package Server
 * @subpackage Registry
 * @filesource
 */
/***/
require_once 'Singleton.interface.php';
require_once 'Server/DataStore.class.php';
/**
 * Register class with nested keys
 *
 * Singleton implementation of the Registry pattern for storing and
 * retrieving values with keys.
 *
 * @package Server
 * @subpackage Registry
 */
class Registry extends DataStore implements Singleton{

    /**
     * Holds self reference
     *
     * @var Registry
     */
    static private $instance = null;
    
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
        throw new Exception('Cloning is disabled!');
    }

    /**
     * Returns instance of class
     *
     * @return self
     */
    public function getInstance()
    {
        if(self::$instance === null){
            self::$instance = new self;
        }
        return self::$instance;
    }
}
?>