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
namespace Seraphp\Server;
require_once 'Server/Daemon.interface.php';
require_once 'Comm/Ipc/IpcFactory.class.php';
require_once 'Log/LogFactory.class.php';
/**
 * Implementation of Daemon interface
 *
 * Couples all required responsibilities to create a Server process
 *
 * @package Server
 */
abstract class Server implements Daemon
{
    /**
     * @var string  Current status of the instance
     */
    public $status = 'exists';

    /**
     * @var boolean  If false new process not created
     */
    public $daemonize = true;

    /**
     * Logging engine reference if any
     * @var Log
     */
    protected static $_log = null;
    /**
     * Maximum number of allowed child processes. Default is 5.
     * @var integer
     */
    private $_maxSpawns = 5;

    /**
     * Actual role of the process (could be 'parent'(in the main process),
     * or 'child' in the forked subprocesses.
     * @var string
     */
    protected $_role = 'parent';
    /**
     * Stores existing child processes
     * @var array
     */
    protected $_spawns = array();
    /**
     * @var string
     */
    protected $_pidFile = null;
    /**
     * @var string
     */
    protected $_pidFileName = '';
    /**
     * @var string
     */
    protected $_pidFolder = null;
    /**
     * Own PID (process id)
     * @var integer
     */
    protected $_pid;
    /**
     * The IpcAdapter instance reference used or Null if there is no need for
     * one
     *
     * @var IpcAdapter|Null
     */
    protected $_ipc = null;
    /**
     * Class name of the IpcAdapter class which is used, if any
     * @var string
     */
    protected $_ipcType = '';

    /**
     * All the available signal in the system
     * @var array
     */
    protected $_availSigs = array();

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
        self::$_log = \Seraphp\Log\LogFactory::getInstance();
        ini_set('max_execution_time', 0);
        ini_set('max_input_time', 0);
        set_time_limit(0);
        $this->_ipcType = $ipcType;
        $this->_pid = getmypid();
        $consts = get_defined_constants(true);
        $this->_availSigs = $consts['pcntl'];
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
        if ($this->_role == 'child') {//we are the new process
            $this->_savePid2File();
            $this->setUpSigHandlers();
            //changing back as from now on we are the Parent server process
            $this->_role = 'parent';
            if ($this->_ipcType !== '') {
                $this->_ipc = \Seraphp\Comm\Ipc\IpcFactory::get(
                    $this->_ipcType, $this->_pid
                );
                $this->_ipc->setRole($this->_role);
            }
            $this->onSummon();
            $this->startHart();
        } else {
            //we are the parent process
            return $pid;
        }
    }

    /**
     * Setting up signal handlers for defined callback functions
     *
     * Method will search for defined methods in the class which has a name
     * ending as 'Callback'
     *
     * @return void
     */
    protected function setUpSigHandlers()
    {
        foreach (array_flip($this->_availSigs) as $signal) {
            //SIGKILL cannot be overwriten, SIGCHILD has own handler.
            if ( ($signal !== 'SIGKILL') &&
                is_callable(array($this,strtolower($signal).'Callback'))
            ) {
                if (false === pcntl_signal(
                    constant($signal),
                    array($this,'signalHandler'),
                    true
                )
                ) {
                    self::$_log->warning(
                        sprintf(
                            'Registering signal handler for %s (%d) failed',
                            $signal,
                            constant($signal)
                        )
                    );
                }
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
    private function _savePid2File()
    {
        $this->_pidFile = fopen(
            $this->_pidFolder.'/'.
            $this->_pidFileName,
            "w"
        );
        if (!$this->_pidFile || !flock($this->_pidFile, LOCK_EX | LOCK_NB)) {
            throw new \Exception('Unable to get pid file lock!');
        }
        fwrite($this->_pidFile, $this->_pid);
        fflush($this->_pidFile);
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
        $this->status = 'running';
        declare(ticks = 1);
        while (true) {
            if ($this->daemonize === true) {
                declare(ticks = 0);
                $this->hartBeat();
                declare(ticks = 1);
            } else {
                break;
            }
        }
        declare(ticks = 0);
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
        if ($this->daemonize) {
            if (count($this->_spawns) < $this->_maxSpawns) {
                $pid = pcntl_fork();
                if ($pid < 0) {
                    throw new \Exception('Unable to fork!');
                } elseif ($pid == 0) {//child process
                    self::$_log->setEventItem('pid', getmypid());
                    $this->_pid = getmypid();
                    $this->_role = 'child';
                    if ($this->_ipcType !== '') {
                        $this->_ipc = \Seraphp\Comm\Ipc\IpcFactory::get(
                            $this->_ipcType, posix_getppid()
                        );
                    }
                    //returns own pid
                    return $this->_pid;
                } else {
                    //parent process
                    $this->_pid = getmypid();
                    $this->_spawns[$pid] = array('ipc'=>$this->_ipcType);
                    //returns child's pid
                    return $pid;
                }
            }
        } else {
            $this->_pid = getmypid();
            return $this->_pid;
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
     * Sends SIGTERM to all existing child process and waits for them to exit.
     * Closes IpcAdapter connections and tries to remove the pidfile created
     * for this process.
     *
     * @return boolean
     */
    public function expel()
    {
        $this->onExpel();
        $this->daemonize = false;
        $this->_shutdown();
        return true;
    }

    /**
     * Process shutdown method
     *
     * Sends SIGTERM to all existing child process and waits for them to exit.
     * Closes IpcAdapter connections and tries to remove the pidfile created
     * for this process.
     *
     * @return void
     */
    protected function _shutdown()
    {
        foreach ($this->_spawns as $pid=>$details) {
            if (true === posix_kill($pid, 0)) {
                posix_kill($pid, SIGTERM);
            }
        }
        pcntl_wait($temp = 0);
        if (false === pcntl_wifexited($temp)) {
            self::$_log->warn('Some child process(es) not exited correctly!');
        }
        if (is_resource($this->_pidFile)) {
            flock($this->_pidFile, LOCK_UN);
            $pidData = stream_get_meta_data($this->_pidFile);
            fclose($this->_pidFile);
            unlink(realpath($pidData['uri']));
        }
        $this->status = 'expeled';
    }

    /**
     * Sets maximum allowed number of child processes
     * @param integer $num
     * @return integer Actual number of allowed child processes
     */
    public function setMaxSpawns($num)
    {
        $this->_maxSpawns = $num;
        return $this->_maxSpawns;
    }

    /**
     * Get actual number of allowed child processes
     * @return integer
     */
    public function getMaxSpawns()
    {
        return $this->_maxSpawns;
    }

    /**
     * Main signal handler method, calling callback functions for signals.
     *
     * @param $sigCode
     * @return void
     */
    private function signalHandler($sigCode)
    {
        switch($sigCode) {
            /*
            Here For every signal we re-register the signal handler before doing
            everything else, to avoid race condition, the situation when a
            second signal arrives before the first one waould be processed.
            */
            case SIGCHLD:
                pcntl_signal(SIGCHLD, array($this, 'signalHandler'), true);
                while (($pid = pcntl_wait($sigCode, WNOHANG)) > 0) {
                    //Handling all exited child with this 'while'
                    $this->sigchldCallback($pid, pcntl_wexitstatus($sigCode));
                }
                break;
            default:
                $sigName = array_keys($this->_availSigs, $sigCode);
                if (is_array($sigName)) {
                    foreach ($sigName as $signal) {
                        $method = strtolower($signal).'Callback';
                        if (method_exists($this, $method)) {
                            pcntl_signal(
                                $sigCode,
                                array($this,'signalHandler'),
                                true
                            );
                            call_user_func(array($this, $method));
                        }
                    }
                } elseif ($sigName !== false) {
                    pcntl_signal($sigCode, array($this, 'signalHandler'), true);
                    call_user_func(
                        array($this, strtolower($sigName).'Callback')
                    );
                }
                return;
                break;
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
     * Gives back status of the Server instance
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function getRole()
    {
        return $this->_role;
    }

    abstract protected function onSummon();
    abstract protected function onExpel();
}