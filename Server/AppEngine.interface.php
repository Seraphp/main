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
require_once 'Server/Config/Config.class.php';
/**
 * Defines the methodes an AppEngine should implement
 *
 * @package Server
 */
interface AppEngine
{
    /**
     * A Constructor must exists accepting the configuration
     *
     * @param Config $conf  Configuration part from xml
     * @return void
     */
    function __construct(Config $conf);
    /**
     * Method to process the received Request
     *
     * @param Request $req  Incoming request in wrapper object
     * @return integer  exit statuscode
     */
    function process(Request $req);
}