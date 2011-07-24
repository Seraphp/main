<?php
/**
 * Holds IpcUnixsockets class implementation
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id:IpcUnixsockets.class.php 289 2008-12-31 17:13:49Z peter $
 * @copyright Copyright (c) 2008, Peter Nagy
 * @package Comm
 * @subpackage Ipc
 * @filesource
 */
/***/
namespace Seraphp\Comm\Ipc;
require_once 'Comm/Ipc/IpcAdapter.interface.php';
/**
 * Interprocess communication through unix sockets
 *
 * @package Comm
 * @subpackage Ipc
 */
class IpcUnixsockets implements IpcAdapter
{

    /**
     * @var resource  Reference for the child end of the socket pair
     */
    private $_sockChild = null;
    /**
     * @var resource  Reference for the parent end of the socket pair
     */
    private $_sockParent = null;
    /**
     * @var boolean  True if child, false if parent
     */
    private $_role = false;
    /**
     * @var integer  Process ID of the current process
     */
    private $_pid = null;
    /**
     * @var string Line ending to use in messages
     */
    private $_ln = "\n";

    /**
     * @var integer Maximum message length available
     */
    const MAX_MESSAGE_LENGTH = 50000;

    /* (non-PHPdoc)
     * @see Comm/Ipc/IpcAdapter#init($pid, $role)
     */
    public function init($pid, $role)
    {
        $this->close();
        // Opening a pair of unix sockets, which are indistinguishable and
        // connected
        $sockets = stream_socket_pair(
            STREAM_PF_UNIX,
            STREAM_SOCK_STREAM,
            STREAM_IPPROTO_IP
        );
        foreach ($sockets as $sock) {
            //setting socket to non-blocking mode adn immediately write
            stream_set_blocking($sock, 0);
            stream_set_write_buffer($sock, 0);
        }
        $this->_pid = $pid;
        $this->_role = ($role == 'child');
        $this->_sockChild = $sockets[0];
        $this->_sockParent = $sockets[1];
        return $this->getRole();
    }

    /* (non-PHPdoc)
     * @see Comm/Ipc/IpcAdapter#getRole()
     */
    public function getRole()
    {
        return ($this->_role)?'child':'parent';
    }

    /* (non-PHPdoc)
     * @see Comm/Ipc/IpcAdapter#setRole($role)
     */
    public function setRole($role)
    {
        if ($this->_role != ($role == 'child')) {
              $this->_role = ($role == 'child');
              $this->roleChange();
        }
        return $this->getRole();
    }

    /**
     * @return string  Child or PArent, depending on the current role
     */
    private function roleChange()
    {
        $this->init($this->_pid, $this->_role);
        return $this->getRole();
    }

    /* (non-PHPdoc)
     * @see Comm/Ipc/IpcAdapter#read()
     */
    public function read()
    {
        $read = array(($this->_role)?$this->_sockChild:$this->_sockParent);
        if (stream_select($read, $w = null, $e = null, 0, 500) > 0) {
            unset($w, $e);
            return stream_get_line(
                ($this->_role)?$this->_sockChild:$this->_sockParent,
                self::MAX_MESSAGE_LENGTH,
                $this->_ln
            );
        } else {
            return null;
        }
    }

    /* (non-PHPdoc)
     * @see Comm/Ipc/IpcAdapter#write($to, $message)
     */
    public function write($to, $message)
    {
        $res = fwrite(
            ($this->_role)?$this->_sockChild:$this->_sockParent,
            $message.$this->_ln
        );
        return $res;
    }

    /* (non-PHPdoc)
     * @see Comm/Ipc/IpcAdapter#close()
     */
    public function close()
    {
        if (is_resource($this->_sockChild)) {
            stream_socket_shutdown($this->_sockChild, STREAM_SHUT_RDWR);
        }
        if (is_resource($this->_sockParent)) {
            stream_socket_shutdown($this->_sockParent, STREAM_SHUT_RDWR);
        }
    }

    /**
     * Calls $this->close() before reaching object's life end
     *
     * @return void
     */
    public function __destruct()
    {
        $this->close();
    }
}