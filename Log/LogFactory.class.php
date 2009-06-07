<?php
/**
 * Holds LogFactory class implementation
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @copyright Copyright (c) 2009, Peter Nagy
 * @filesource
 * @package Log
 */
/***/
//namespace Seraphp\Log
require_once 'Exceptions/LogException.class.php';
require_once 'Log/SeraphpLog.class.php';
/**
 * LogFactory class instantiate a logger class instance using PEAR
 *
 * Uses a PEAR::Log composite object as logging facility and based on the
 * provided configuration it is able to instantiate any kind of log target
 * which is know by PEAR::Log.
 * The configuration has to be a SimpleXML object referencing to a structure
 * like the following:
 * <logs>
 *   <log handler="console" name="" ident="Seraphp" level="PEAR_LOG_ERR">
 *     <conf stream="STDOUT" buffering="false" />
 *   </log>
 *   <log handler="file" name="" ident="DEBUG" level="PEAR_LOG_ALL">
 *     <conf />
 *   </log>
 * </logs>
 *
 * @package Log
 * @uses PEAR::Log
 */
class LogFactory
{

    private static $_instance = null;

    private function __construct()
    {//private constructor disabled
    }

    private function __clone()
    {//private cloning disabled
    }

    /**
     * @param Config $conf (Optional)
     * @return SeraphpLog
     */
    public static function getInstance(Config $conf = null)
    {
        if (self::$_instance === null) {
            self::$_instance = SeraphpLog::singleton('composite');
        }
        self::_setup($conf);
        return self::$_instance;
    }

    /**
     * Sets up log handlers
     *
     * Based on the provided configuration or a default one the method
     * configures the log handlers hierarchy and stores it in self::$_instance
     *
     * @param Config $conf
     * @return void
     * @throws LogException
     */
    private static function _setup(Config $conf = null)
    {
        self::_defaultSetup();
        if ($conf !== null && isset($conf->logs) ) {
            $loggers = $conf->logs->children();
            foreach ($loggers as $logger) {
                $attributes = $logger->conf->attributes();
                if (isset($logger['handler']) && defined($logger['level'])) {
                    $handler = SeraphpLog::singleton($logger['handler'],
                            $logger['name'],
                            $logger['ident'],
                            $attributes,
                            constant($logger['level']));
                    $res = self::$_instance->addChild($handler);
                    if ($res === false) {
                        throw new LogException('Invalid log handler at '.
                            $logger->asXML());
                    }
                } else {
                    throw new LogException('Invalid log handler at '.
                        $logger->asXML());
                }
            }
        }
    }

    /**
     * @return void
     */
    protected static function _defaultSetup()
    {
        $setup = array('buffering' => true);
        $console = SeraphpLog::singleton('console',
            '',
            'SeraPhp',
            $setup,
            PEAR_LOG_INFO);
        $file = SeraphpLog::singleton('file',
            'out.log',
            'DEBUG',
            $setup,
            PEAR_LOG_DEBUG);
        self::$_instance->addChild($console);
        self::$_instance->addChild($file);
    }
}