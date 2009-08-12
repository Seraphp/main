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
require_once 'Log/LogFactory.class.php';
/**
 * Default AppEngine class
 *
 * This class will be used if no Engine was given to an instantiated AppServer.
 * The class simply display a message to the requestor, saying server is
 * up and running.
 *
 * @package Server
 */
class DefaultEngine implements AppEngine
{
    private static $_log;

    function __construct(Config $conf)
    {
    }

    function process(Request $req)
    {
        self::$_log = LogFactory::getInstance();
        self::$_log->debug(__METHOD__.' called');
        $msg = <<<HTML
    <html>
        <head>
            <title>Seraphp</title>
        </head>
            <body><h1>It Works!</H1>
        </body>
    </html>
HTML;
        $response = $req->respond($msg);
        $response->contentType = 'text/html';
        self::$_log->debug($response);
        try{
            $response->send();
        } catch (IOException $e) {
            return 1;
        }
        return 0;
    }
}
