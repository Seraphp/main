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
namespace Seraphp\Server\Registry;
require_once 'StoreEngine.interface.php';
require_once 'Exceptions/IOException.class.php';
/**
 * Store engine to save|load|export store content into a file
 *
 * @package Server
 * @subpackage Registry
 * @todo Debug why "compress.zlib://" and such protocols are not working
 */
class PackedFileDataStore implements StoreEngine
{

    const FN_PREFIX = "srpd";

    public $protocol = "file://";

    /**
     * @var string  Full path to used file for storing data
     */
    private $_file = '';
    /**
     * @var resource  File pointer reference
     */
    private $_fp = null;

    private $_dirty = false;

    /**
     * Calls init() if a file is provided
     * @see init()
     * Sets protocol if provided
     *
     * @param string $file  Optional, filepath to be used
     * @param string $protocol  OPtional, protocol to be used
     * @return void
     */
    function __construct($file = null, $protocol = null)
    {
        if ($protocol !== null) {
            $this->protocol = $protocol;
        }
        if ($file !== null) {
            $this->setUp($file);
        }
    }

    /**
     * @see Server/Registry/StoreEngine#init()
     *
     * @param string $file Optional, filepath to be used
     * @return true  On success
     * @throws IOException
     */
    function setUp($file = null)
    {
        $this->setPath($file);
        touch($this->_file);
        $this->_open();
        $this->_close();
        return true;
    }

    /**
     * Tries to open the file for use.
     *
     * Throws IOException if file cannot be open or unable to set exceptional
     * lock on it. Return true on success.
     *
     * @return boolean
     * @throws IOException
     */
    protected function _open()
    {
        $this->_fp = fopen($this->protocol.$this->_file, 'r+');
        if (!is_resource($this->_fp)) {
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
    function load()
    {
        if (!is_resource($this->_fp)) {
            $this->_open();
        }
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
        if (!is_resource($this->_fp)) {
            $this->_open();
        }
        $res = fwrite($this->_fp, base64_encode(serialize($data)));
        if ($res === false) {
            throw new IOException('Error when writing file '.$this->_file);
        }
        fflush($this->_fp);
        $this->_close();
        return true;
    }

    protected function _close()
    {
        if (is_resource($this->_fp)) {
            flock($this->_fp, LOCK_UN);
            fclose($this->_fp);
        }
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
        if (null === $path) {
            $path = tempnam(getcwd(), self::FN_PREFIX);
        } else {
            $path = $this->_getAbsolutePath($path);
        }
        if (is_dir($path)) {
            $path = tempnam($path, self::FN_PREFIX);
        }
        if (is_writable(dirname($path))) {
            $this->_file = $path;
            return $this->_file;
        } else {
            throw new \Seraphp\Exceptions\IOException(
                'Path '.dirname($path).' is not writable'
            );
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
            $absolutes = array_filter(
                explode(DIRECTORY_SEPARATOR, $cwd), 'strlen'
            );
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
        return DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $absolutes);
    }

    /**
     * @return string  Canonical absolute path with filename to use
     * for storing data
     */
    function getPath()
    {
        return $this->_file;
    }
}