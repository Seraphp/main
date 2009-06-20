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
class DefaultEngine implements AppEngine
{
    private static $_log;

    function process(Request $req)
    {
        self::$_log = LogFactory::getInstance();
        self::$_log->debug(__METHOD__.' called');
        self::$_log->debug('Output buffer ended');
        self::$_log->debug('message length: '. strlen($req->httpRawHeaders));
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
        $response->send();
        return $response->statusCode;
    }
}
