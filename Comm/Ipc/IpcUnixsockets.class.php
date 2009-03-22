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
require_once 'Comm/Ipc/IpcAdapter.interface.php';
/**
 * Interprocess communication through unix sockets
 *
 * @package Comm
 * @subpackage Ipc
 */
class IpcUnixsockets implements IpcAdapter
{

    private $_sockChild = null;
    private $_sockParent = null;
    private $_role = false;
    private $_pid = null;
    private $_ln = "\n";

    const MAX_MESSAGE_LENGTH = 50000;

    public function init($pid, $role)
    {
        $this->close();
        //opening a pair of unix sockets, which are indistinguishable and
        // connected
        $sockets = stream_socket_pair(STREAM_PF_UNIX,
            STREAM_SOCK_STREAM,
            STREAM_IPPROTO_IP);
        foreach ($sockets as $sock) {
            //setting socket to non-blocking mode
            stream_set_blocking($sock, 0);
        }
        $this->_sockChild = $sockets[0];
        $this->_sockParent = $sockets[1];
        $this->_pid = $pid;
        $this->_role = ($role == 'child');
        return $this->getRole();
    }

    public function getRole()
    {
        return ($this->_role)?'child':'parent';
    }

    public function setRole($role)
    {
        if ($this->_role != ($role == 'child')) {
              $this->_role = ($role == 'child');
              $this->roleChange();
        }
        return $this->getRole();
    }

    private function roleChange()
    {
        $this->init($this->_pid, $this->_role);
    }

    public function read()
    {
        return
          stream_get_line(($this->_role)?$this->_sockChild:$this->_sockParent,
                            self::MAX_MESSAGE_LENGTH,
                            $this->_ln);
    }

    public function write($to, $message)
    {
        return fwrite(($this->_role)?$this->_sockChild:$this->_sockParent,
            $message.$this->_ln);
    }

    public function close()
    {
        if (is_resource($this->_sockChild)) {
            fclose($this->_sockChild);
        }
        if (is_resource($this->_sockParent)) {
            fclose($this->_sockParent);
        }
    }

    public function __destruct()
    {
        $this->close();
    }
}