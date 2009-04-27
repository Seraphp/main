<?php
/**
 * File documentation
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @copyright Copyright (c) 2009, Peter Nagy
 * @filesource
 */
/**
 * Class documentation
 */
class ExceptionHandler
{
    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public static function printException(Exception $e)
    {
        print 'Uncaught '. get_class($e).
        ', code: '. $e->getCode() ."\n".
        'Message: '.$e->getMessage()."\n";
    }

    public static function handleException(Exception $e)
    {
         self::printException($e);
    }

    public static function setup()
    {
        return set_exception_handler(array('ExceptionHandler', 'handleException'));
    }

    public static function recall()
    {
        return restore_exception_handler();
    }
}
