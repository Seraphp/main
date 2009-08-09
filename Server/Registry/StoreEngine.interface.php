<?php
/**
 * Contains StoreEngine interface definition
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @copyright Copyright (c) 2009, Peter Nagy
 * @package Server
 * @subpackage Registry
 * @filesource
 */
/**
 * Defines how data in a store will be handled
 *
 * @package Server
 * @subpackage Registry
 */
interface StoreEngine
{
    /**
     * Initalize the engine before any other action can be taken on it
     *
     * @param resource $resource
     * @return boolean
     */
    public function init($resource = null);
    /**
     * Loads the given resource and rebuild data store in memory
     *
     * @param $resource
     * @return boolean
     */
    public function load($resource = null);
    /**
     * Saves the actual data from store to the represented format
     *
     * @param mixed $storeData
     * @return boolean
     */
    public function save($storeData);
    /**
     * Close the datastore
     *
     * @return void
     */
    public function close();
    /**
     * Clens up any bits and pieces before saving
     *
     * @return void
     */
    public function cleanUp();
}