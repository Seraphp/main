<?php
/**
 * Contains PackedFile data store facility class
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @copyright Copyright (c) 2009, Peter Nagy
 * @package Server
 * @subpackage Registry
 * @filesource
 */
/***/
//namespace Seraphp\Server\Registry
require_once 'StoreEngine.interface.php';
require_once 'Exceptions/IOException.class.php';
/**
 * Store engine to save|load|export store content into a file
 *
 * @package Server
 * @subpackage Registry
 * @todo Debug why "compress.zlib://" and suh protocols are not working
 */
class PackedFileDataStore implements StoreEngine
{

    const FN_PREFIX = "srpd";
    const PROTOCOL = "file://";
    private static $_lastFile = '';

    /**
     * @var string  Full path to used file for storing data
     */
    private $_file = '';
    /**
     * @var resource  File pointer reference
     */
    private $_fp = null;

    /**
     * Calls init() with parameter
     * @see init()
     *
     * @param string $file  Optional, filepath to be used
     * @return void
     */
    function __construct($file = null)
    {
        if (isset($file)) {
            $this->init($file);
        }
    }

    /**
     * @see Server/Registry/StoreEngine#init()
     *
     * @param string $file Optional, filepath to be used
     * @return true  On success
     * @throws IOException
     */
    function init($file = null)
    {
        $this->setPath($file);
        touch($this->_file);
        return $this->_open();
    }

    private function _reinit()
    {
        $this->close();
        return $this->_open();
    }

    protected function _open()
    {
        $this->_fp = fopen(self::PROTOCOL.$this->_file, 'r+');
        if (!$this->_fp) {
            throw new IOException('Cannot open file '.$this->_file);
        }

        if (flock($this->_fp, LOCK_EX) === false) {
            throw new IOException('Cannot get lock on '.$this->_file);
        }
        return true;
    }

    /**
     * @see Server/Registry/StoreEngine#load()
     */
    function load($file = null)
    {
        if (isset($file) && $this->_file !== $this->_getAbsolutePath($file)) {
            $this->close();
            $this->init($file);
        }
        rewind($this->_fp);
        $data = fgets($this->_fp);
        if (!feof($this->_fp)) {
            if ($data === false) {
                throw new IOException('Error when reading file '.$this->_file);
            }
        }
        if ($data !== false) {
            return unserialize(base64_decode($data));
        } else {
            return array();
        }
    }

    /**
     * @see Server/Registry/StoreEngine#save()
     *
     * @return true  On success
     * @throws IOException
     */
    function save($data)
    {
        rewind($this->_fp);
        $res = fwrite($this->_fp, base64_encode(serialize($data)));
        if ($res === false) {
            throw new IOException('Error when writing file '.$this->_file);
        }
        fflush($this->_fp);
        return true;
    }

    function close()
    {
        if (is_resource($this->_fp)) {
            flock($this->_fp, LOCK_UN);
            fclose($this->_fp);
        }
    }

    /**
     * Calls save() and release file when called
     * @see save()
     *
     * @return void
     */
    function __destruct()
    {
        $this->close();
    }

    /**
     * Set path with filename to use for storing data
     * If given string is a file it will return canonical absolute path
     * or False on error
     *
     * @param string $path
     * @return string|false
     */
    function setPath($path)
    {
        clearstatcache();
        if (empty($path)) {
            if (empty(self::$_lastFile)) {
                $path = tempnam(getcwd(), self::FN_PREFIX);
            } else {
                $path = self::$_lastFile;
            }
        } else {
            $path = $this->_getAbsolutePath($path);
        }
        if (is_dir($path)) {
            $path = tempnam($path, self::FN_PREFIX);
        }
        if (is_writable(dirname($path))) {
            $this->_file = $path;
            self::$_lastFile = $this->_file;
            return $this->_file;
        } else {
            throw new IOException('Path '.dirname($path).' is not writable');
        }
    }



    /**
     * Returns absolute filepath fo given path
     *
     * As realpath does not handle non-existing files I have to use this ű
     * user-land implementation based on a comment on php.net.
     *
     * @param string $path
     * @return string  Absolute canonical path
     */
    private function _getAbsolutePath($path)
    {
        if (file_exists($path)) {
            return realpath($path);
        }
        $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        if ($parts[0] === '.') {
            $cwd = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, getcwd());
            $absolutes = array_filter(explode(DIRECTORY_SEPARATOR, $cwd,
                'strlen'));
        } else {
            $absolutes = array();
        }
        foreach ($parts as $part) {
            if ('.' == $part) continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        return implode(DIRECTORY_SEPARATOR, $absolutes);
    }

    /**
     * @return string  Canonical absolute path with filename to use
     * for storing data
     */
    function getPath()
    {
        return $this->_file;
    }

    function cleanUp()
    {
        clearstatcache();
        //because: strlen(base64_encode(serialize(array()))) = 8
        if (filesize($this->_file) < 9) {
            unlink($this->_file);
        }
    }
}