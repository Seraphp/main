<?php
/**
 * Holds default AppEngine implementation class
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @copyright Copyright (c) 2009, Peter Nagy
 * @package Server
 * @filesource
 */
/***/
require_once 'Server/AppEngine.interface.php';
/**
 * Default AppEngine class
 *
 * This class will be used if no Engine was given to an instantiated AppServer.
 * The class simply display a message to the requestor, saying server is
 * up and running.
 *
 * @package Server
 * @todo Implement Request handling
 */
class DefaultEngine implements AppEngine{

    function process(Request $req)
    {
        return 200;
    }
}
?>