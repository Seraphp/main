<?php
/**
 * Contains AppEngine interface definition
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @copyright Copyright (c) 2009, Peter Nagy
 * @filesource
 * @package Server
 */
/***/
require_once 'Comm/Request.interface.php';
/**
 * Defines the methodes an AppEngine should implement
 *
 * @package Server
 */
interface AppEngine{

    /**
     * Enter description here...
     *
     * @param Request $req
     * @return integer
     */
    function process(Request $req);
}
?>