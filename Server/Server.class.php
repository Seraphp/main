<?php
/**
 * Contains main server implementation
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @copyright Copyright (c) 2008, Peter Nagy
 * @package Server
 * @filesource
 */
/***/
require_once 'Server/Daemon.interface.php';
require_once 'Comm/Ipc/IpcFactory.class.php';
/**
 * Implementation of Daemon interface
 *
 * Couples all required responsibilities to create a Server process
 *
 * @package Server
 */
abstract class Server implements Daemon{
    private $maxSpawns = 5;
    protected $role = 'parent';
    protected $spawns = array();
    protected $pidFile = null;
    protected $pidFolder = null;
    protected $pid;
    protected $ipc = null;
    protected $ipcType = '';

    public function __construct($ipcType = '')
    {
        ini_set('max_execution_time',0);
        ini_set('max_input_time',0);
        set_time_limit(0);
        $this->ipcType = $ipcType;
        $this->pid = getmypid();
    }

    final public function summon()
    {
        $this->spawn();
        if($this->role == 'child')
        {//we are the new process
            $this->savePid2File();
            pcntl_signal(SIGCHLD, array($this,'signalHandler'),true);
            pcntl_signal(SIGUSR1, array($this,'signalHandler'),true);
            //changing back as from now on we are the Parent server process
            $this->role == 'parent';
            fputs(STDOUT, 'Server process (pid:'.$this->pid.") summoned\n");
            $this->startHart();
        }
        else
        {//we are the parent process
            return true;
        }
    }

    private function savePid2File()
    {
        $this->pidFile = fopen($this->pidFolder.'/.phaser'.$this->appID.'.pid', "a");
        if (!$this->pidFile || !flock($this->pidFile, LOCK_EX | LOCK_NB))
        {
            throw new Exception('Unable to get pid file lock!');
        }
        fwrite($this->pidFile, $this->pid);
        fflush($this->pidFile);
    }

    protected function startHart()
    {
        while(true){
            usleep(500);
        }
    }

    public function spawn()
    {
        if (count($this->spawns) < $this->maxSpawns)
        {
            $ipc = ($this->ipcType !== '')?IpcFactory::get($this->ipcType,$this->pid):null;
            $pid = pcntl_fork();
            if($pid < 0)
            {
                throw new Exception('Unable to fork!');
            }
            elseif($pid == 0)
            {
                $this->pid = getmypid();
                $this->role = 'child';
                if($ipc !== null)
                {
                    $ipc->setRole($this->role);
                }
                $this->ipc = $ipc;
            }
            else
            {
                if($ipc !== null)
                {
                    $ipc->setRole($this->role);
                }
                $this->spawns[] = array('pid'=>$pid, 'ipc'=>$ipc);
            }
            fputs(STDOUT, $pid." spawned\n");
        }
    }

    public function expell()
    {
        fputs(STDOUT, "children: \n");
        $success = false;
        foreach($this->spawns as $child)
        {
            fputs(STDOUT, $child['pid']."\n");
            posix_kill ($child['pid'], SIGSTOP);
            pcntl_waitpid ($child['pid'], $temp = 0, WNOHANG);
            $success = pcntl_wifexited($temp);
            if($success && $child['ipc'] !== null)
            {
                $child['ipc']->close();
            }
        }
        flock($this->pidFile, LOCK_UN);
        fclose($this->pidFile);
        unlink($this->pidFile);
        fputs(STDOUT, "Parent exiting..");
        return $success;
    }

    public function setMaxSpawns($num)
    {
        $this->maxSpawns = $num;
    }

    public function getMaxSpawns()
    {
        return $this->maxSpawns;
    }

    private function signalHandler($sigCode)
    {
        switch($sigCode){
            case 0:
                return;
            break;
            case SIGCHILD:
                pcntl_signal(SIGCHLD, array($this,'signalHandler'),true);
                fputs(STDOUT, 'signal received:'.$this->lastSignal);
                fputs(STDOUT, ' means SIGCHILD...');
                $this->expell();
            break;
            case SIGUSR1:
                pcntl_signal(SIGUSR1, array($this,'signalHandler'),true);
                fputs(STDOUT, 'signal received:'.$this->lastSignal);
                fputs(STDOUT, ' means SIGUSR1...');
                $this->expell();
            break;
            default:
                fputs(STDOUT, 'signal received:'.$this->lastSignal);
                fputs(STDOUT, ' a strange signal');
        }
    }

    public function __destruct()
    {
        if($this->pidFile !== null)
        {
            $this->expell();
        }
    }
}
?>