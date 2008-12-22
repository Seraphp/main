<?php
/**
 * Holds IpcUnixsockets class implementation
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @copyright Copyright (c) 2008, Peter Nagy
 * @filesource
 */
/***/
require_once 'Comm/Ipc/Ipc.interface.php';
/**
 * Interprocess communication through unix sockets
 * TODO: Add JSON stream wrapper to socket transport (see stream_register_wrapper)
 */
class IpcUnixsockets implements Ipc{

    private $sockChild = null;
    private $sockParent = null;
    private $role = false;
    private $pid = null;
    private $ln = "\n";

    const MAX_MESSAGE_LENGTH = 50000;

    public function init($pid, $role)
    {
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
    }

    public function getRole()
    {
        return ($this->role)?'child':'parent';
    }

    public function setRole($role)
    {
        //a process needs only one side of the socket
        if($role == 'child')
        {
            fclose($this->sockParent);
        }
        else
        {
            fclose($this->sockChild);
        }
        $this->role = ($role == 'child');
    }

    public function read()
    {
        return stream_get_line(($this->role)?$this->sockChild:$this->sockParent,self::MAX_MESSAGE_LENGTH,$this->ln);
    }

    public function write($to, $message)
    {
        fwrites(($this->role)?$this->sockChild:$this->sockParent);
    }

    public function close()
    {
        if($this->role)//when we are in the child process
        {
            fclose($this->sockChild);
        }
        else
        {
            fclose($this->sockParent);
        }
    }

    public function _destruct()
    {
        $this->close();
    }
}
?>