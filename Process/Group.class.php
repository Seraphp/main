<?php
/**
 *
 * File holds a class repsresenting a group
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
 * Class representing a group in the op.system
 *
 * Class represents a group specified by a group id or group.
 * It wraps the posix_* calls basically * into an object oriented API.
 *
 * @package Process
 *
 */
class Group implements \Iterator
{
    /**
     * Group ID on the system
     *
     * @var integer
     */
    private $_gid = null;
    private $_groupName = null;
    private $_members = array();
    private $_pointer = 0;

    /**
     * Constructor of Group object
     *
     * Accepts an integer as group id or a string as group name
     *
     * @param integer|string $gid
     * @throws ProcessException
     */
    function __construct($gid)
    {
        switch (true) {
            case is_numeric($gid):
                $info = posix_getgrgid($gid);
                break;
            case is_string($gid):
                $info = posix_getgrnam($gid);
                break;
            default:
                throw new ProcessException('No groupid provided');
        }
        $this->_gid = $info['gid'];
        $this->_groupName = $info['name'];
        $this->_members = $info['members'];
    }

    /**
     * Gets a n array of User objects which are members of this group
     *
     * @return array
     */
    function getMembers()
    {
        return Factory::createMany('user', $this->_members);
    }

    /**
     * Returns groupid as integer
     * @return integer
     */
    function getId()
    {
        return $this->_gid;
    }

    /**
     * Returns group name as string
     * @return string
     */
    function getName()
    {
        return $this->_groupName;
    }

    function current()
    {
        return Factory::create(
        	'user',
            $this->_members[$this->_pointer]
        );
    }

    function rewind()
    {
        $this->_pointer = 0;
    }

    function key()
    {
        return $this->_members[$this->_pointer];
    }

    function next()
    {
        ++$this->_pointer;
    }

    function valid()
    {
        return isset($this->_members[$this->_pointer]);
    }
}