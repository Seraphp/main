<?php
/**
 * Contains main server implementation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
require_once 'Server/Daemon.interface.php';
/**
 * Implementation of Daemon interface
 * 
 * Couples all required responsibilities to create a Server process
 * 
 * @package Server
 */
abstract class Server implements Daemon{
    private $maxSpawns = 5;
    protected $spawns = array();
    protected $pidFile = null;
    protected $pidFolder = null;
    protected $pid;
    
    public function __construct(){
        ini_set('max_execution_time',0);
        ini_set('max_input_time',0);
        set_time_limit(0);
    }
    
    final public function summon(){
        $this->pidFile = fopen($this->pidFolder.'/.'.$this->appID.'.pid', "a");
        if (!$this->pidFile || !flock($this->pidFile, LOCK_EX | LOCK_NB))
        {
            fputs(STDERR, "Failed to acquire lock\n");
            exit;
        }
        $pid = pcntl_fork();
        if ($pid < 0)
        {
            return false;
        }
        elseif($pid==0)
        {//child process go here
            
            posix_setsid();
            $this->spawn();
            fwrite($this->pidFile, $this->pid);
            fflush($this->pidFile);
            pcntl_signal(SIGCHLD, array($this,'signalHandler'),true);
            pcntl_signal(SIGUSR1, array($this,'signalHandler'),true);
            $this->startHart();
        }
        else 
        {//we are the parent process
            return true;
        }
    }
    
    protected function startHart(){
        while(true){
            usleep(500);
        }
    }
    
    public function spawn(){
        if (count($this->spawns) < $this->maxSpawns)
        {
            if($pid = pcntl_fork())
            {
                $this->pid = getmypid();
                $this->spawns[] = $pid; 
                //fputs(STDOUT, var_dump($this));
            }
            /*else
            {
                exit;
            }*/
        }
    }
    
    public function expell(){
        fputs(STDOUT, 'shuting down: '.$this->appID."\n");
        fclose($this->pidFile);
        unlink($this->pidFolder.'/.'.$this->appID.'.pid');
    }
    
    public function setMaxSpawns($num){
        $this->maxSpawns = $num;
    }
    
    private function signalHandler($sigCode){
        switch($sigCode){
            case 0:
                return;
            break;
            case SIGCHILD:
                fputs(STDOUT, 'signal received:'.$this->lastSignal);
                fputs(STDOUT, ' means SIGCHILD...');
                pcntl_signal(SIGCHLD, array($this,'signalHandler'),true);
                $this->expell();
            break;
            case SIGUSR1:
                fputs(STDOUT, 'signal received:'.$this->lastSignal);
                fputs(STDOUT, ' means SIGUSR1...');
                pcntl_signal(SIGUSR1, array($this,'signalHandler'),true);
                $this->expell();
            break;
            default:
                fputs(STDOUT, 'signal received:'.$this->lastSignal);
                fputs(STDOUT, ' a strange signal');
        }
    }
}
?>