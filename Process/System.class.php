<?php
namespace Seraphp\Process;
require_once 'Exceptions/ProcessException.class.php';
use \Seraphp\Exceptions\ProcessException;

class System
{
    private $_signals = array();
    private static $_instance = null;

    /**
     * Private constructor disables direct instantiation
     */
    private function __construct()
    {
        $this->_collectSignals();
    }

    /**
     * Disables cloning
     */
    private function __clone()
    {
    }

    /**
     * Gets a Singleton instance from the class
     *
     * @return System
     */
    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Collects the available signal constant's from the system.
     */
    private function _collectSignals()
    {
        $consts = get_defined_constants(true);
        $this->_signals = array_filter(
            array_keys($consts['pcntl']),
            array($this,'_isSignalName')
        );
    }

    /**
     * Filter method for signal collection
     *
     * @param  $str
     * @return boolean
     */
    private function _isSignalName($str)
    {
        return (boolean)preg_match("/^SIG([alpha]*)/", $str);
    }

    /**
     * Return current working directory as string
     *
     * @return string
     * @throws Exceptions\ProcessException
     */
    public function getCWD()
    {
        $cwd = posix_getcwd();
        if (false === $cwd) {
            throw new ProcessException();
        }
        return $cwd;
    }

    /**
     * Return access right for the file given with full path
     *
     * Returned array will contain 2 keys and a boolean true if the right is
     * given for that operation. In opposite case the value of the array element
     * will be an ProcessException object, describing error in detais.
     *
     * @param string $file
     * @throws Exceptions\ProcessException
     * @return array
     */
    public function checkAccess($file)
    {
        $access = array();
        $rights = array(
            'read'=>POSIX_R_OK,
            'write'=>POSIX_W_OK,
            'exec'=>POSIX_X_OK
        );
        if (posix_access($file, POSIX_F_OK)) {
            foreach ($rights as $right=>$code) {
                $access[$right] = posix_access($file, $code);
                if (false === $access[$right]) {
                    $access[$right] = new ProcessException();
                }
            }
        } else {
            throw new ProcessException();
        }
        return $access;
    }

    /**
     * Returns resource limits of current system.
     *
     * If system not supports such query an empty array wll be returned.
     *
     * @return array|boolean
     */
    public function getResourceLimits()
    {
        if (function_exists('posix_getrlimit')) {
            return posix_getrlimit();
        } else return false;
    }

    /**
     * Identifies if a a file descriptor is a terminal
     *
     * @param resource $rsc
     * @return boolean
     * @throws Exceptions\ProcessException
     */
    public function isTerminal($rsc)
    {
        if ($this->_isFileDescriptor($rsc)) {
            return posix_isatty($rsc);
        } else {
            throw new ProcessException(
                'Argument is not a valid file descriptor'
            );
        }
    }

    /**
     * Tells if resource is a file descriptor.
     * @param unknown_type $rsc
     * @return boolean
     */
    private function _isFileDescriptor($rsc)
    {
        return (is_resource($rsc) && get_resource_type($rsc)=='stream');
    }

    /**
     * Creates a special FIFO file
     *
     * It exists in the file system and acts as a bidirectional communication
     * endpoint for processes.
     * The second parameter mode has to be given in octal notation (e.g. 0644).
     * The permission of the newly created FIFO also depends on the setting of
     * the current umask(). The permissions of the created file are
     * (mode & ~umask).
     *
     * @param string $path
     * @param number $mode
     */
    public function createFIFO($path, $mode)
    {
        $res = @posix_mkfifo($path, $mode);
        if (false === $res) {
            throw new ProcessException();
        }
        return $res;
    }

    /**
     * Create a special or ordinary file
     *
     * see:posix_mknod
     *
     * @param string $path
     * @param number $mode
     * @param number $type
     * @param integer $major
     * @param integer $minor
     * @throws Exceptions\ProcessException
     * @return boolean
     */
    public function createNode(
        $path, $mode=0700, $type=POSIX_S_IFREG, $major=0, $minor=0
    )
    {
        $res = @posix_mknod($path, $type|$mode, $major, $minor);
        if (false === $res) {
            throw new ProcessException();
        }
        return $res;
    }

    /**
     * Determine terminal device name
     *
     * @param resource $rsc
     * @return boolean
     * @throws Exceptions\ProcessException
     */
    public function getTerminalName($rsc)
    {
        if (true === $this->_isFileDescriptor($rsc)) {
            if ($this->isTerminal($rsc)) {
                return posix_ttyname($rsc);
            } else {
                throw new ProcessException(
                    'File descriptor is not a terminal'
                );
            }
        } else {
            throw new ProcessException(
                'Argument is not a valid file descriptor'
            );
        }
    }

    /**
     * Gets information about the system.
     *
     *  Returns a hash of strings with information about the system. The indices
     *  of the hash are:
     *  - sysname - operating system name (e.g. Linux)
     *  - nodename - system name (e.g. valiant)
     *  - release - operating system release (e.g. 2.2.10)
     *  - version - operating system version (e.g. #4 Tue Jul 20 17:01:36 MEST
     *  1999)
     *  - machine - system architecture (e.g. i586)
     *  - domainname - DNS domainname (e.g. example.com)
     *  domainname is a GNU extension and not part of POSIX.1, so this field is
     *  only available on GNU systems or when using the GNU libc.
     *
     * @return array
     */
    public function getSystemInfo()
    {
        return posix_uname();
    }

    /**
     * Tells if the given signal is supported by the operating system
     *
     * @param integer $signal
     */
    public function isSignalSupported($signal)
    {
        return in_array($signal, $this->_signals);
    }
}