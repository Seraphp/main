<?php
/**
 * Contains main AppServer class implementation
 * 
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @package Server
 * @filesource
 */
//namespace Phaser::Server;
require_once 'Server/Server.class.php';
require_once 'Comm/Request.interface.php';
/**
 * AppServer implementation class
 * @package Server
 */
class AppServer extends Server{
    
    protected $appID = '';
    private $includeFolder = '';
    protected $pidFolder = '/home/peter/workspace/phaser';
    
    public function __construct($appID)
    {
        $this->appID = $appID;
        parent::__construct();
    }
    
    public function getAppId(){
        return $this->appId;
    }
        
    public function process(Request $request){}
    
}
?>