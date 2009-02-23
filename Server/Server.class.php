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
//namespace Seraphp\Server;
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

    /**
     * Maximum number of allowed child processes. Default is 5.
     * @var integer
     */
    private $maxSpawns = 5;

    /**
     * Actual role of the process (could be 'parent'(in the main process),
     * or 'child' in the forked subprocesses.
     * @var string
     */
    protected $role = 'parent';
    /**
     * Stores existing child processes
     * @var array
     */
    protected $spawns = array();
    /**
     * @var string
     */
    protected $pidFile = null;
    /**
     * @var string
     */
    protected $pidFolder = null;
    /**
     * Own PID (process id)
     * @var integer
     */
    protected $pid;
    /**
     * The IpcAdapter instance reference used or Null if there is no need for one
     * @var IpcAdapter|Null
     */
    protected $ipc = null;
    /**
     * Class name of the IpcAdapter class which is used, if any
     * @var string
     */
    protected $ipcType = '';

    /**
     * All the available signal in the system
     * @var array
     */
    protected $availSigs = array();

    /**
     * Constructor of the Server process
     *
     * Set PHP to no timeout, and if an IpcAdapter name was given,
     * it stores it.
     *
     * @param string $ipcType  (Optional) set used IpcAdapter class's name
     * @return Server
     */
    public function __construct($ipcType = '')
    {
        ini_set('max_execution_time',0);
        ini_set('max_input_time',0);
        set_time_limit(0);
        $this->ipcType = $ipcType;
        $this->pid = getmypid();
        $consts = get_defined_constants(true);
        $this->availSigs = $consts['pcntl'];
        unset($consts);
    }

    /**
     * Initiate daemonization process
     *
     * Opens pidfile, sets up signal handlers and start the infinite
     * loop of the process.
     *
     * @return boolean
     */
    final public function summon()
    {
        $pid = $this->spawn();
        if($this->role == 'child')
        {//we are the new process
            $this->savePid2File();
            $this->setUpSigHandlers();
            //changing back as from now on we are the Parent server process
            $this->role = 'parent';
            if($this->ipcType !== '')
            {
                $this->ipc = IpcFactory::get($this->ipcType,$this->pid);
                $this->ipc->setRole($this->role);
            }
            fputs(STDOUT, 'Server process (pid:'.$this->pid.") summoned\n");
            $this->onSummon();
            $this->startHart();
        }
        else
        {//we are the parent process
            return $pid;
        }
    }

    /**
     * Setting up signal handlers for defined callback functions
     *
     * Method will search for defined methods in the class which has a name ending as 'Callback'
     *
     * @return void
     */
    protected function setUpSigHandlers()
    {
        fputs(STDOUT, "setUpSigHandlers() called\n");
        foreach (array_flip($this->availSigs) as $signal)
        {
        	//SIGKILL cannot be overwriten, SIGCHILD has own handler.
            if( ($signal !== 'SIGKILL') &&
                is_callable(array($this,strtolower($signal).'Callback'))
            )
        	{
        	    fputs(STDOUT, 'Registering signal handler for '.$signal.'('.constant($signal).')...');
        	    if(pcntl_signal(constant($signal), array($this,'signalHandler'),true))
        	        fputs(STDOUT, "OK\n");
        	    else
        	        fputs(STDOUT, "Failed\n");
        	}
        }
    }

    /**
     * Opens a pidfile for the process
     *
     * The opened file will be blocked to write for other processes.
     * Method will write process id into the file.
     *
     * @throws Exception  If unable to get a lock on the file
     * @return void
     */
    private function savePid2File()
    {
        $this->pidFile = fopen($this->pidFolder.'/.seraphp'.$this->appID.'.pid', "a");
        if (!$this->pidFile || !flock($this->pidFile, LOCK_EX | LOCK_NB))
        {
            throw new Exception('Unable to get pid file lock!');
        }
        fwrite($this->pidFile, $this->pid);
        fflush($this->pidFile);
    }

    /**
     * Start infinite loop of the process
     *
     * In every cycle it calls hartBeat() method to perform actions.
     *
     * @return void
     */
    protected function startHart()
    {
        declare(ticks = 1);
        while(true)
        {
            $this->hartBeat();
        }
    }

    /**
     * Create a new child process
     *
     * If the maximum allowed numbers of child processes not reached, it will
     * fork a new one. Throws an exception if not able to fork.
     *
     * @return void
     * @throws Exception
     */
    public function spawn()
    {
        if (count($this->spawns) < $this->maxSpawns)
        {
            $pid = pcntl_fork();
            if($pid < 0)
            {
                throw new Exception('Unable to fork!');
            }
            elseif($pid == 0)
            {//child process
                $this->pid = getmypid();
                $this->role = 'child';
                if($this->ipcType !== '')
                {
                    $this->ipc = IpcFactory::get($this->ipcType, posix_getppid());
                }
                return $this->pid;
            }
            else
            {//parent process
                $this->spawns[$pid] = array('ipc'=>$ipc);
                fputs(STDOUT, $pid." spawned\n");
                return $pid;
            }
        }
    }

    /**
     * Called continuasly at every cycle of the process's hart.
     * @see Server::startHart()
     *
     * @return void
     */
    abstract protected function hartBeat();

    /**
     * Process shutdown method
     *
     * Sends SIGSTOP to all existing child process and waits for them to exit.
     * Closes IpcAdapter connections and tries to remove the pidfile created
     * for this process.
     *
     * @return boolean
     */
    public function expell()
    {
        $this->onExpell();
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
        $pidData = stream_get_meta_data($this->pidFile);
        fclose($this->pidFile);
        fputs(STDOUT, "deleting ".$pidData['uri']."\n");
        unlink($pidData['uri']);
        fputs(STDOUT, "Parent exiting..\n");
        return $success;
    }

    /**
     * Sets maximum allowed number of child processes
     * @param integer $num
     * @return integer Actual number of allowed child processes
     */
    public function setMaxSpawns($num)
    {
        $this->maxSpawns = $num;
        return $this->maxSpawns;
    }

    /**
     * Get actual number of allowed child processes
     * @return integer
     */
    public function getMaxSpawns()
    {
        return $this->maxSpawns;
    }

    /**
     * Main signal handler method, calling callback functions for signals.
     *
     * @param $sigCode
     * @return void
     */
    private function signalHandler($sigCode)
    {
        fputs(STDOUT, __METHOD__." called\n");
        switch($sigCode)
        {
        /*
        Here For every signal we re-register the signal handler before doing everything else,
        to avoid race condition, the situation when a second signal arrives before the first
        one waould be processed.
        */
            case SIGCHLD:
                pcntl_signal(SIGCHLD, array($this,'signalHandler'),true);
                while(($pid=pcntl_wait($sigCode, WNOHANG))>0)
                {//Handling all exited child with this 'while'
                    $this->sigchldCallback($pid, pcntl_wexitstatus($sigCode));
                }
            break;
            default:
                $sigName = array_keys($this->availSigs,$sigCode);
                if(is_array($sigName))
                {
                    foreach($signame as $signal)
                    {
                        if(is_callable($this,strtolower($sigName).'Callback'))
                        {
                            pcntl_signal($sigCode, array($this,'signalHandler'),true);
                            call_user_func(array($this,strtolower($sigName).'Callback'));
                        }
                    }
                }
                elseif($sigName !== false)
                {
                    pcntl_signal($sigCode, array($this,'signalHandler'),true);
                    call_user_func(array($this,strtolower($sigName).'Callback'));
                }
                return;
        }
    }


    /**
     * Callback function called when a child process exited
     *
     * @param integer $pid  PID of the Child process which exited
     * @param integer $status  Exit status of the child process
     * @return void
     */
    abstract protected function sigchldCallback($pid, $status);

    /**
     * Destructor method
     *
     * If we are the parent process and closing, will call expell();
     * @see Server::expell()
     *
     * @return void
     */
    public function __destruct()
    {
        if($this->role === 'parent' && is_resource($this->pidFile))
        {
            $this->expell();
        }
    }

    abstract protected function onSummon();
    abstract protected function onExpell();
}
?>
