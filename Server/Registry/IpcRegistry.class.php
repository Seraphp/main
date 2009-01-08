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
require_once 'Comm/Ipc/IpcAdapter.interface.php';
/**
 * A singleton registry which communicate any changes through IPC with
 * ithe parent process process.
 *
 * Setting a value stored in the registry will be propagated to the parent
 * process which is connected to the same IPC channel through IpcAdapter class.
 * @package Server
 * @subpackage Registry
 * @todo Test the class
 */
class IpcRegistry extends Registry{

    /**
     * The IpcAdapter class to perform interprocess communication
     *
     * @var IpcAdapter
     */
    protected $ipc = null;
    /**
     * Array holding keys of changed values in registry
     *
     * @var array
     */
    protected $changedKeys = array();

    /**
     * Adds the IpcAdapter for the class
     *
     * @param IpcAdapter $ipc
     */
    public function useIpc(IpcAdapter $ipc)
    {
        $this->ipc = $ipc;
    }

    /**
     * Additionally to the parent, this method logs the changed keys.
     *
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        if(parent::__set($key, $value) === true)
        {
            $this->changedKeys[] = $key;
        }
    }

    public function mergeChanges($changes)
    {
        if(is_array($changes))
        {
            $this->store = array_merge($this->store, $changes);
            return count($changes);
        }
        else return 0;
    }
    /**
     * Method send all the changed values to the parent process and sets to default the dirty flag.
     *
     * @return integer  Number of changed values in registry
     */
    public function save($changes = null)
    {
        if($this->dirty === true)
        {
            $num = count($this->changedKeys);
            $changed = array();
            for ($idx = 0; $idx<$num; $idx++)
            {
                $changed[$this->changedKeys[$idx]] = $this->store[$this->changedKeys[$idx]];
            }
            $this->ipc->write($changed);
            $this->dirty = false;
            $this->changedKeys = array();
            return $num;
        }
        else return 0;
    }

    public function __destruct()
    {
        $this->save();
    }
}
?>