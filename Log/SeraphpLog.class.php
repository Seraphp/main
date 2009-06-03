<?php
/**
 Holds a Log decorator class
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @copyright Copyright (c) 2009, Peter Nagy
 * @package Log
 * @filesource
 */
/***/
//namespace Seraphp\Log;
require_once 'Log.php';
/**
 * Decorator for Log, to add PID to Identification string
 * @package Log
 */
class SeraphpLog extends Log{
    function setIdent($str)
    {
        parent::setIdent(sprintf('%s(%d)',$str,getmypid()));
    }
}