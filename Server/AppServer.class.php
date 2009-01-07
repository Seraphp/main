<?php
/**
 * Contains main AppServer class implementation
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @copyright Copyright (c) 2008, Peter Nagy
 * @version $Id$
 * @package Server
 * @filesource
 */
/***/
//namespace Phaser::Server;
require_once 'Server/Server.class.php';
require_once 'Server/Registry/AppServerRegistry.class.php';
require_once 'Comm/Request.interface.php';
/**
 * AppServer implementation class
 *
 * @package Server
 */
class AppServer extends Server{

    protected $appID = '';
    private $includeFolder = '';
    protected $pidFolder = '/home/peter/workspace/phaser';
    protected $appReg = null;
    private $engine = null;

    public function __construct($appID, $engine, $ipcType='unixsockets')
    {
        $this->appID = $appID;
        $this->appReg = AppServerRegistry::getInstance();
        $this->engine = $engine;
        parent::__construct($ipcType);
    }

    public function getAppId()
    {
        return $this->appID;
    }

    public function process(Request $req)
    {
        return $this->engine->process($req);
    }

    public function expell()
    {
        fputs(STDOUT, 'shuting down: '.$this->appID."\n");
        parent::expell();
    }
}
?>