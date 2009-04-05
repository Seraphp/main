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
interface StoreEngine {
    public function init();
    public function load();
    public function save();
    public function export();
}