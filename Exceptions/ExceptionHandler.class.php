<?php
/**
 * File contains Exception Handler class implementation
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @copyright Copyright (c) 2009, Peter Nagy
 * @package Log
 * @filesource
 */
/***/
namespace Seraphp\Exceptions;
require_once 'Log/LogFactory.class.php';
require_once 'NestedException.class.php';
/**
 * Static class for saetting up & handlin Exception centrally
 * @package Log
 */
class ExceptionHandler
{

    private static $_log;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * Logs exception to default log class as Alert
     *
     * @param Exception $e
     * @return void
     */
    public static function handleException(\Exception $e)
    {
        if (self::$_log === null) {
            self::setup();
        }
        if (get_class($e) === "\Seraphp\Exceptions\LogException") {
           throw $e;
        } else {
            try{
                self::$_log->alert($e->getMessage());
            }catch(\Exception $f){
                throw $f->setPrior($e);
            }
        }
    }

    /**
     * Replaces the system default exception handler with this class
     *
     * @param Config $conf
     * @return string  Previous Exception handler if any
     */
    public static function setup(Config $conf = null)
    {
        if ($conf === null) {
            self::$_log = \Seraphp\Log\LogFactory::getInstance();
        } else {
            self::$_log = \Seraphp\Log\LogFactory::getInstance($conf->logs);
        }
        return set_exception_handler(
            array('\Seraphp\Exceptions\ExceptionHandler', 'handleException')
        );
    }

    /**
     * Sets back to previous setup the default system exceptin handler
     * @return string  Previous Exception handler if any
     */
    public static function recall()
    {
        return restore_exception_handler();
    }
}
