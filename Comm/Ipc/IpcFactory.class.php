<?php
/**
 * Holds IpcFactory class implementation
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @copyright Copyright (c) 2008, Peter Nagy
 * @package Comm
 * @subpackage Ipc
 * @filesource
 */
/***/
require_once 'Comm/Ipc/Ipc.interface.php';
require_once 'Exceptions/PluginException.class.php';
/**
 * Static class to retrieve Ipc implementation classes
 *
 * Retreive already initalized InterProcess Communication imlementation
 * classes, depending on the given type. Class checks if the requested
 * implementation type is exists in the filesystem. Requesting such not
 * implemented types results throwing Exception.
 */
class IpcFactory{

    private static $pluginsDir  = '';
    /**
     * Returns a object which implements Ipc interface
     *
     * @param string $type
     * @param integer $pid
     * @return Ipc
     * @throws PluginException
     * @static
     */
    static function get($type,$pid)
    {
        if(self::isValidIpc($type))
        {
            $className = self::getClassName($type);
            $class = new $className;
            $class->init($pid, 'child');
            return $class;
        }
    }

    static function getClassName($type)
    {
        return sprintf('Ipc%s',ucfirst($type));
    }

    static function setPluginsDir($dir = '')
    {
        if(empty($dir))
        {
            $dir = dirname(__FILE__);
        }
        if(is_dir($dir))
        {
            self::$pluginsDir = $dir;
            return true;
        }
        else return false;
    }

    static function getPluginsDir()
    {
        return self::$pluginsDir;
    }

    /**
     * Checks if given type has implementation class
     *
     * Classes has to be named as Ipc<Type>.class.php to make IpcFactory
     * recognize them.
     * For example:
     *   IpcPipe.class.php will be recognized as "pipe".
     *
     * @param string $plugin
     * @throws PluginException
     */
    private static function isValidIpc($type)
    {
        self::setPluginsDir();
        $pluginFile = sprintf('%s/%s.class.php',self::$pluginsDir,self::getClassName($type));
        if(is_file($pluginFile))
        {
            require_once $pluginFile;
            $class = new ReflectionClass(self::getClassName($type));
            if($class->implementsInterface('Ipc'))
            {
                return true;
            }
            else throw new PluginException('Ipc interface not implemented in '.$type);
        }
        else throw new PluginException(self::getClassName($type).'.class.php not exists!');
    }
}
?>