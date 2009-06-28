<?php
/**
 * Hold implementation of JSON-RPC Response class
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @copyright Copyright (c) 2009, Peter Nagy
 * @package Comm
 * @subpackage JsonRpc
 * @filesource
 */
/***/
//namespace Seraphp\Comm;
/**
 * Implements simple JSON-RPC Response class
 *
 * @package Comm
 * @subpackage JsonRpc
 * @since JSON-RPC 1.0
 */
class JsonRpcResponse
{
    public $result = null;
    public $error = null;
    public $id = null;

    public function __construct($result, $error = null, $id = null)
    {
        if ( $result === null && $error === null ) {
            throw new Exception('Either a result or an error should be set!');
        } else {
            $this->error = $error;
            $this->result = $result;
            $this->id = $id;
        }
    }

    public function __toString()
    {
        return json_encode($this);
    }
}