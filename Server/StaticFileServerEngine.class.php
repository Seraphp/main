<?php
/**
 * Holds StaticFileServerEngine implementation class
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
 * StaticFileServerEngine class
 *
 * This class serves static files as is for when they are requested.
 * It identify the file requested reads it and send it out with the correct
 * http response.
 *
 * @package Server
 * @todo Implement Request handling
 */
class StaticFileServerEngine implements AppEngine
{
    private static $_log;
    protected $_basePath = '';

    public function __construct(Config $conf = null)
    {
        self::$_log = LogFactory::getInstance($conf->server);
        $param = $conf->xsearch('srph:param[@name="basepath"]');
        if ($param !== false) {
            $this->_basePath = (string)$param[0];
        }
    }

    function process(Request $req)
    {
        $truePath = $this->_basePath.
            strtr(
                substr($req->url, strpos($req->url, '/', 1)),
                PATH_SEPARATOR,
                DIRECTORY_SEPARATOR
            );
        if (file_exists($truePath)) {
            $returnCode = 0;
            $resp = $req->respond(fopen($truePath, 'rb'));
        } else {
            $returnCode = 1;
            $resp = $req->respond('File not found');
            $resp->statusCode = 404;
        }
        $resp->send();
        return $returnCode;
    }
}