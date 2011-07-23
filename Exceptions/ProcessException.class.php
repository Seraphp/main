<?php
/**
 * Holds ProcessException implementation
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @copyright Copyright (c) 2010, Peter Nagy
 * @package Exceptions
 * @filesource
 */
namespace Seraphp\Exceptions;
/**
 * ProcessException class
 *
 * @package Exceptions
 */
class ProcessException extends \Exception
{
    function __construct($msg=null, $id=null, \Exception $prev=null)
    {
        if (is_null($msg) && is_null($id)) {
            $id = posix_get_last_error();
            $msg = posix_strerror($id);
        }
        parent::__construct($msg, $id, $prev);
    }
}