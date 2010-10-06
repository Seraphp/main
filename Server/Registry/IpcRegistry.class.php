<?php
/**
 * Holds IpcRegistry class implementation
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @copyright Copyright (c) 2009, Peter Nagy
 * @package Server
 * @subpackage Registry
 * @filesource
 */
/***/
namespace Seraphp\Server\Registry;
require_once 'Server/Registry/Registry.class.php';
require_once 'Comm/Ipc/IpcAdapter.interface.php';
/**
 * A singleton registry which communicate any changes through IPC with
 * ithe parent process process.
 *
 * Setting a value stored in the registry will be propagated to the parent
 * process which is connected to the same IPC channel through IpcAdapter class.
 *
 * @package Server
 * @subpackage Registry
 * @todo Test the class
 */
class IpcRegistry extends Registry
{

    /**
     * The IpcAdapter class to perform interprocess communication
     *
     * @var IpcAdapter
     */
    protected $_ipc = null;
    /**
     * Array holding keys of changed values in registry
     *
     * @var array
     */
    protected $_changedKeys = array();

    private static $_instance = null;

    /**
     * Disabled constructor
     *
     */
    private function __construct()
    {
    }

    /**
     * Disabled cloning facility to preserve only 1 instance
     * @throws Exception  if used
     */
    public function __clone()
    {
        throw new \Exception('Cloning is disabled!');
    }

    /**
     * Returns instance of class
     *
     * @return self
	*/
    public function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    /**
     * Adds the IpcAdapter for the class
     *
     * @param IpcAdapter $ipc
     */
    public function useIpc(\Seraphp\Comm\Ipc\IpcAdapter $ipc)
    {
        $this->_ipc = $ipc;
    }

    /**
     * Additionally to the parent, this method logs the changed keys.
     *
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        if (parent::__set($key, $value) === true) {
            $this->_changedKeys[] = $key;
        }
    }

    public function mergeChanges()
    {
        $changes = unserialize($this->_ipc->read());
        if (is_array($changes)) {
            $this->_store = array_merge($this->_store, $changes);
            return count($changes);
        }
        else return 0;
    }
    /**
     * Method send all the changed values to the parent process and sets to
     * default the dirty flag.
     *
     * @return integer  Number of changed values in registry
     */
    public function save($changes = null)
    {
        if ($this->_dirty === true) {
            $num = count($this->_changedKeys);
            $changed = array();
            for ($idx = 0; $idx<$num; $idx++) {
                $changed[$this->_changedKeys[$idx]] =
                $this->_store[$this->_changedKeys[$idx]];
            }
            $this->_ipc->write($changed);
            $this->_dirty = false;
            $this->_changedKeys = array();
            return $num;
        }
        else return 0;
    }

    public function __destruct()
    {
        $this->save();
    }
}