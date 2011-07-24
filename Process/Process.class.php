<?php
/**
 *
 * File holds a class repsresenting a process
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @copyright Copyright (c) 2009, Peter Nagy
 * @package Process
 * @filesource
 */
namespace Seraphp\Process;
require_once 'Factory.class.php';
require_once 'System.class.php';
require_once 'Exceptions/ProcessException.class.php';
use \Seraphp\Exceptions\ProcessException;

/**
 *
 * Class representing a process
 *
 * Class represents a process specified by a process id, or the current one if
 * no PID were given to the constructor. It wraps the posix_* calls basically
 * into an object oriented API.
 *
 * @package Process
 *
 */
class Process
{
    /**
     * Tell is object wraps current process
     * @var boolean
     */
    private $_thatsMe = false;
    /**
     * Process ID of this process
     * @var integer
     */
    private $_pid = null;
    /**
     * Reference of the System object of this process
     * @var \Process\System
     */
    private $_sys = null;
    /**
     * Full path of the interactive terminal(tty) if any
     * @var string
     */
    private $_terminal = null;

    /**
     * Constructor, accepts a process ID as optional parameter.
     *
     * Withour argument the current process will be wraped by the object.
     *
     * @param integer $pid
     */
    function __construct($pid = null)
    {
        if ($pid === null) {
            $pid = posix_getpid();
        }
        if (!is_integer($pid)) {
            throw new ProcessException('Only integer process id accepted');
        }
        $this->_pid = $pid;
        $this->_init();
    }

    /**
     * Initalize the object
     *
     * @throws Exceptions\ProcessException
     */
    private function _init()
    {
        if ($this->_pid === posix_getpid()) {
            $this->_thatsMe = true;
        } else {
            $this->_thatsMe = false;
        }
        if (false === ($this->_terminal = posix_ctermid())) {
            throw new ProcessException();
        }
        $this->_sys = System::getInstance();
    }

    /**
     * Returns the process id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->_pid;
    }

    /**
     * Gets the User object of the process.
     *
     * @param boolean $effective  Default=true
     * @return \Seraphp\Process\User
     * @throws Exceptions\ProcessException
     */
    public function getUser($effective = true)
    {
        $user = (true === $effective)?posix_geteuid():posix_getuid();
        return Factory::create('user', $user);
    }

    /**
     * Set the owner of the process
     *
     * Can set real or effective userid.
     *
     * @param User $usr
     * @param boolean $effective  To set effective(true, default) userid.
     */
    public function setUser(User $usr, $effective = true)
    {
        if (true === $effective) {
            return posix_seteuid($usr->getId());
        } else {
            return posix_setuid($usr->getId());
        }
    }

    /**
     * Returns a Group object related to the process..
     *
     * @param integer|string $grp  (optional)
     * @param boolean $effective  Default=true
     * @return \Seraphp\Process\Group
     */
    public function getGroup($effective = true)
    {
        $grp = (true === $effective)?posix_getegid():posix_getgid();
        return Factory::create('group', $grp);
    }

    /**
     * Set group id of process to group
     *
     * Needs privileged user to succeed
     *
     * @param Group $grp
     * @param boolean $effective  To set effective (true, default) or real gid
     * @return boolean
     */
    public function setGroup(Group $grp, $effective = true)
    {
        if (true === $effective) {
            return posix_setegid($grp->getId());
        } else {
            return posix_setgid($grp->getId());
        }
    }

    /**
     * Returns a User object related to the process...
     *
     * @return \Seraphp\Process
     */
    public function getParent()
    {
        return Factory::create('process', posix_getppid());
    }

    /**
     * Returns the ID of the process group the process is currently member of
     *
     * @return integer  processgroup ID
     */
    public function getProcGrp()
    {
        return posix_getpgrp();
    }

    /**
     * Sets processgroup of the process for job control.
     *
     * @param integer $pgid Process group ID
     * @param integer $pid Process ID, default to current if null
     * @return boolean
     */
    public function addProc2ProcGrp($pgid, $pid=null)
    {
        if (is_null($pid)) {
            $pid = $this->_pid;
        }
        return posix_setpgid($pid, $pgid);
    }

    /**
     * Returns the ID of the session the given process is currently member of
     *
     * @param Process $prc (optional)
     * @return integer process session ID
     */
    public function getSessionID(Process $prc=null)
    {
        if (is_null($prc)) {
            $pid = 0;
        } else {
            $pid = $prc->getId();
        }
        return posix_getsid($pid);
    }

    /**
     * Process become session leader in op.sys
     * @return integer
     * @throws Exceptions\ProcessException
     */
    public function becomeSessionLeader()
    {
        $id = @posix_setsid();
        if ($id === -1) {
            throw new ProcessException();
        } else {
            return $id;
        }
    }

    /**
     * Fork a new child process from the current on.
     *
     * Returns thenew process as object in the parent process, while in the
     * newly created child it gives back a self reference.
     *
     * @return Process\Process
     * @throws Exceptions\ProcessException
     */
    public function fork()
    {
        $newPid = @pcntl_fork();
        switch ($newPid) {
            case -1://error
                throw new ProcessException();
                break;
            case 0://child proces
                $this->_pid = posix_getpid();
                $this->_init;
                return $this;
                break;
            default://parent process
                return Factory::create('process', $newPid);
        }
    }

    /**
     *  Returns a hash of strings with information about the process's CPU
     *  usage.
     *
     *  The indices of the hash are:
     *	- ticks - the number of clock ticks that have elapsed since reboot.
     *  - utime - user time used by the current process.
     *  - stime - system time used by the current process.
     *  - cutime - user time used by current process and children.
     *  - cstime - system time used by current process and children.
     *  @return array
     */
    public function getTimes()
    {
        return posix_times();
    }

    /**
     * Send signal to a process
     *
     * @param Process|integer $target
     * @param integer $signal
     * @throws ProcessException
     * @return boolean
     */
    public function sendSignal($target, $signal)
    {
        switch (gettype($target)) {
            case 'object':
                    if (get_class($target) !== 'Process' &&
                        !is_subclass_of($target, 'Process'))
                    {
                        throw new ProcessException(
                            "Provided object is not Process class's or ".
                            " its subclass's instance"
                        );
                    }
                break;
            case 'integer':
                break;
            default:
                throw new ProcessException(
                    'Only Process object or integer accepted'
                );
        }
        if ($this->_sys->isSignalSupported($signal)) {
            throw new ProcessException(
                'Signal not supported by op. system'
            );
        }
        if (false === posix_kill($target, $signal)) {
            throw new ProcessException();
        } else {
            return true;
        }
    }
}