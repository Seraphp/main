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
 * @package Comm
 * @subpackage Ipc
 */
class IpcUnixsockets implements IpcAdapter{

    private $sockChild = null;
    private $sockParent = null;
    private $role = false;
    private $pid = null;
    private $ln = "\n";

    const MAX_MESSAGE_LENGTH = 50000;

    public function init($pid, $role)
    {
        $this->close();
        //opening a pair of unix sockets, which are indistinguishable and connected
        $sockets = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
        foreach($sockets as $sock)
        {
            stream_set_blocking($sock, 0); //setting socket to non-blocking mode
        }
        $this->sockChild = $sockets[0];
        $this->sockParent = $sockets[1];
        $this->pid = $pid;
        $this->role = ($role == 'child');
        return $this->getRole();
    }

    public function getRole()
    {
        return ($this->role)?'child':'parent';
    }

    public function setRole($role)
    {
        if($this->role != ($role == 'child'))
        {
              $this->role = ($role == 'child');
              $this->roleChange();
        }
        return $this->getRole();
    }

    private function roleChange()
    {
        $this->init($this->pid, $this->role);
    }

    public function read()
    {
        return stream_get_line(($this->role)?$this->sockChild:$this->sockParent,self::MAX_MESSAGE_LENGTH,$this->ln);
    }

    public function write($to, $message)
    {
        return fwrite(($this->role)?$this->sockChild:$this->sockParent, $message.$this->ln);
    }

    public function close()
    {
        if(is_resource($this->sockChild))
        {
            fclose($this->sockChild);
        }
        if(is_resource($this->sockParent))
        {
            fclose($this->sockParent);
        }
    }

    public function __destruct()
    {
        $this->close();
    }
}
?>