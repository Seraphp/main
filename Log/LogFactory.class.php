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
namespace Seraphp\Log;
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
 *
 * @uses Zend_Log
 * @uses PEAR::Log
 */
class LogFactory
{

    /**
     * @var array  Stores the instance with an own key
     */
    private static $_instance = array();
    /**
     * @var string  'Zend' or 'PEAR'
     */
    public static $provider = '';

    /**
     * Disabled constructor
     * @return void
     */
    private function __construct()
    {
    }

    /**
     * Disabled cloning
     *
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Gives back an instance of the Log facility class
     *
     * @param Config $conf (Optional)
     * @param string $provider Accepted 'Zend' or 'PEAR' (Optional)
     * @return SeraphpLog
     */
    public static function getInstance(\Seraphp\Server\Config\Config $conf = null, $provider = 'Zend')
    {
        if ($conf !== null) {
            $key = md5($provider.$conf->asXml().$provider);
        } else {
            $key = md5($provider.$provider);
        }
        if (self::$_instance === array() ||
          !array_key_exists($key, self::$_instance)) {
            self::$_instance = null;
            self::$provider = '';
            self::_init($provider);
            self::_setup($conf);
        }
        return self::$_instance;
    }

    private static function _init($provider)
    {
        switch ($provider) {
            case 'Zend':
                if (include_once('Zend/Loader/Autoloader.php')) {
                    \Zend_Loader_Autoloader::getInstance();
                    self::$_instance = new \Zend_Log();
                    self::$provider = 'Zend';
                } else {
                    throw new \Exception("Zend_Log package not available!");
                }
                break;
            case 'PEAR':
                if (include_once('Log.php')) {
                    self::$_instance = \Log::singleton('composite');
                    self::$provider = 'PEAR';
                } else {
                    throw new \Exception("PEAR Log package not available!");
                }
                break;
            default:
                if (include_once('Zend/Loader/Autoloader.php')) {
                    \Zend_Loader_Autoloader::getInstance();
                    self::$_instance = new \Zend_Log();
                    self::$provider = 'Zend';
                } elseif (include_once('Log.php')) {
                    self::$_instance = \Log::singleton('composite');
                    self::$provider = 'PEAR';
                } else {
                    throw new Exception('No logger package available'.
                    '(Zend_Log or PEAR::Log)!');
                }
                break;
        }
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
    private static function _setup(\Seraphp\Server\Config\Config $conf = null)
    {
        self::_defaultSetup();
        if ($conf !== null && isset($conf->logs) ) {
            $loggers = $conf->logs->children();
            foreach ($loggers as $logger) {
                if (isset($logger['handler']) && isset($logger['level'])) {
                    self::_addWriter($logger);
                } else {
                    throw new \Seraphp\Exceptions\LogException(
                        'Invalid log handler at '. $logger->asXML()
                    );
                }
            }
        }
    }

    private static function _addWriter($logger)
    {
        if (self::$provider === 'Zend') {
            $level = constant('Zend_Log::'.$logger['level']);
            $writerClass = 'Zend_Log_Writer_'.$logger['handler'];
            $attribs = $logger->conf->attributes();
            switch ($logger['handler']) {
                case 'console':
                    $writer = new \Zend_Log_Writer_Stream('php://output');
                    break;
                case 'file':
                case 'Stream':
                    if (isset($attribs['mode'])) {
                        $stream = @fopen($logger['name'], $attribs['mode'], 0);
                        if (! $stream) {
                            throw new \Exception('Failed to open stream: '.
                            $logger['name']);
                        }
                    } else {
                            $stream = $logger['name'];
                    }
                    $writer = new \Zend_Log_Writer_Stream($stream);
                    break;
                case 'Mail':
                    $mail = new \Zend_Mail();
                    $mail->setFrom((string)$attribs['from'])
                        ->addTo((string)$attribs['to']);
                    if (isset($attribs['layout'])) {
                        $layout = new \Zend_Layout();
                        $format = sprintf(
                            (string) $attribs['layout'],
                            \Zend_Log_Formatter_Simple::DEFAULT_FORMAT
                        );
                        $writer = new $writerClass($mail, $layout);
                        $formatter = new \Zend_Log_Formatter_Simple($format);
                        $writer->setLayoutFormatter($formatter);
                    } else {
                        $writer = new $writerClass($mail);
                    }
                    $writer->setSubjectPrependText((string)$attribs['subject']);
                    break;
                case 'Syslog':
                    $writer = new $writerClass();
                    if (isset($attribs['application'])) {
                        $writer->setApplicationName($attribs['application']);
                    }
                    if (isset($attribs['facility'])) {
                        $writer->setFacility(constant($attribs['facility']));
                    }
                    break;
                default:
                    $writer = new $writerClass();
                    break;
            }

            $format = '%timestamp% %pid% (%priorityName%): %message%' . PHP_EOL;
            $formatter = new \Zend_Log_Formatter_Simple($format);
            $writer->setFormatter($formatter);
            $filter = new \Zend_Log_Filter_Priority($level);
            $writer->addFilter($filter);

            self::$_instance->addWriter($writer);
        } else { //PEAR Logger used
            $level = constant('PEAR_LOG_'.$logger['level']);
            $handler = \Log::singleton(
                (string) $logger['handler'],
                (string) $logger['name'],
                (string) $logger['ident'],
                (string) $logger->conf->attributes(),
                $level
            );
            $res = self::$_instance->addChild($handler);
            if ($res === false) {
                throw new \Seraphp\Exceptions\LogException(
                    'Invalid log handler at '. $logger->asXML()
                );
            }
        }
    }

    /**
     * @return void
     */
    protected static function _defaultSetup()
    {
        if (self::$provider === 'Zend') {
            self::$_instance->setEventItem('pid', getmypid());
            $format = '%timestamp% %pid% (%priorityName%): %message%' . PHP_EOL;
            $formatter = new \Zend_Log_Formatter_Simple($format);

            $debugFilter = new \Zend_Log_Filter_Priority(\Zend_Log::DEBUG);
            $infoFilter = new \Zend_Log_Filter_Priority(\Zend_Log::INFO);

            $console = new \Zend_Log_Writer_Stream('php://output');
            $console->setFormatter($formatter);
            $console->addFilter($infoFilter);
            self::$_instance->addWriter($console);

            $file = new \Zend_Log_Writer_Stream('out.log');
            $file->setFormatter($formatter);
            $file->addFilter($debugFilter);
            self::$_instance->addWriter($file);
        } else {
            $setup = array('buffering' => false);
            $console = \Log::singleton(
                'console',
                '',
                'SeraPhp',
                $setup,
                PEAR_LOG_INFO
            );
            $file = \Log::singleton(
                'file',
                'out.log',
                'DEBUG',
                $setup,
                PEAR_LOG_DEBUG
            );
            self::$_instance->addChild($console);
            self::$_instance->addChild($file);
        }
    }
}