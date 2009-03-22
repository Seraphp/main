<?php
/**
 * Hold implementation of JSON-RPC REquest class
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
require_once 'Comm/Request.interface.class';
/**
 * Implements simple JSON-RPC Request class
 *
 * @package Comm
 * @subpackage JsonRpc
 * @since JSON-RPC 1.0
 */
class Request implements Request
{
     public $id = null;
     public $method = '';
     public $params = array();

     public function __construct($method, $params = array(), $id = null)
     {
         if ( empty($method) ) {
             throw new Exception('Method has to be defined!');
         } else {
             $this->method = $method;
             $this->params = $params;
             $this->id = $id;
         }
     }

     public function __toString()
     {
         return json_encode($this);
     }
}