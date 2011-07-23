<?php
/**
 *
 * File holds a class repsresenting a user
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @copyright Copyright (c) 2009, Peter Nagy
 * @package Process
 * @filesource
 */
namespace Seraphp\Process;
require_once 'Factory.class.php';
require_once 'Exceptions/ProcessException.class.php';
use \Seraphp\Exceptions\ProcessException;
/**
 *
 * Class representing a user in the op.system
 *
 * Class represents a user specified by a user id or username.
 * It wraps the posix_* calls basically * into an object oriented API.
 *
 * @package Process
 *
 */
class User
{
    /**
     * User ID on the system
     *
     * @var integer
     */
    private $_uid = null;
    private $_gid = null;
    private $_userName = null;
    private $_realName = null;
    private $_dir = null;
    private $_shell = null;

    function __construct($uid)
    {
        switch (true) {
            case is_numeric($uid):
                $info = posix_getpwuid($uid);
                break;
            case is_string($uid):
                $info = posix_getpwnam($uid);
                break;
            default:
                throw new ProcessException('No user id provided');
        }
        $this->_uid = $info['uid'];
        $this->_userName = $info['name'];
        $this->_gid = $info['gid'];
        $this->_dir = $info['dir'];
        $this->_shell = $info['shell'];
        $gecos = explode(',', $info['gecos']);
        $this->_realName = $gecos[0];
    }

    /**
     * Returns usergroup as object
     *
     * @param unknown_type $grp
     * @return \Seraphp\Process\Group
     */
    function getGroup()
    {
        return Factory::create('group', $this->_gid);
    }

    /**
     * Returns userid as integer
     * @return integer
     */
    public function getId()
    {
        return $this->_uid;
    }

    /**
     * Returns user's name
     *
     * Either real if known or username.
     *
     * @param boolean $real
     * @return string
     */
    public function getName($real = false)
    {
        if (true === $real && !is_null($this->_realName)) {
            return $this->_realName;
        } else {
            return $this->_userName;
        }
    }

    public function getShell()
    {
        return $this->_shell;
    }

    public function getDir()
    {
        return dir($this->_dir);
    }
}