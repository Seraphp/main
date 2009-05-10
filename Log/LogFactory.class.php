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
require_once 'Log.php';
require_once 'Exceptions/LogException.class.php';
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
class LogFactory{

    private static $_instance = null;

    private function __construct()
    {//private constructor disabled
    }

    private function __clone()
    {//private cloning disabled
    }

    public static function getInstance(SimpleXmlElement $conf = null)
    {
        if (self::$_instance === null) {
            self::_setup($conf);
        }
        return self::$_instance;
    }

    /**
     * Sets up log handlers
     *
     * Based on the provided configuration or a default one the method
     * configures the loge handlers hierarchy and stores it in self::$_instance
     *
     * @param SimpleXml $conf
     * @return void
     * @throws LogException
     */
    private function _setup(SimpleXmlElement $conf = null)
    {
        self::$_instance = Log::singleton('composite');
        if ($conf === null) {
            $console = Log::singleton('console',
                '',
                'SeraPhp',
                null,
                PEAR_LOG_ERR);
            $file = Log::singleton('file',
                '' ,
                'DEBUG',
                null,
                PEAR_LOG_ALL);
            self::$_instance->addChild($console);
            self::$_instance->addChild($file);
        } else {
            //Assume that $conf is a SimpleXml object
            //referencing a <Logs> parent element
            $loggers = $conf->children();
            foreach ($loggers as $logger) {
                $attributes = $logger->conf->attributes();
                if (isset($logger['handler']) && defined($logger['level'])) {
                    $handler = Log::singleton($logger['handler'],
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
}