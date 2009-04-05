<?php
/**
 * Contains PackedFile data store facility class
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @copyright Copyright (c) 2009, Peter Nagy
 * @filesource
 */
/***/
//namespace Seraphp\Server\Registry
require_once 'StoreEngine.interface.php';
require_once 'Exceptions/IOException.class.php';
/**
 * Store engine to save|load|export store content into a file in packed format
 */
class PackedFileDataStore implements StoreEngine{

    /**
     * @var string  Full path to used file for storing data
     */
    private $file = '';
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
        if ($file === null) {
            $file = realpath(tempnam(getcwd(), 'srpd'));
        }
        $this->file = $file;
        $this->_fp = @fopen('compress.zlib://'.$this->file, 'w+b');
        if (!$this->_fp) {
            throw new IOException('Cannot open file '.$file);
        }

        if (@flock($this->_fp, LOCK_EX) === false) {
            throw new IOException('Cannot get lock on '.$file);
        }
        return true;
    }

    /**
     * @see Server/Registry/StoreEngine#load()
     */
    function load($file)
    {
        if ($this->file !== realpath($file)) {
            $this->__destruct();
            $this->file = $file;
            $this->init();
        }
        $data = stream_get_contents($this->_fp);
        if ($data === false) {
            throw new IOException('Error when reading file '.$this->file);
        }
        return unserialize($data);
    }

    /**
     * @see Server/Registry/StoreEngine#save()
     *
     * @return true  On success
     * @throws IOException
     */
    function save($data)
    {
        $res = frwrite($this->_fp, serialize($data));
        if ($res === false) {
            throw new IOException('Error when writing file '.$this->file);
        }
        return true;
    }

    /**
     * Calls save() and release file when called
     * @see save()
     *
     * @return void
     */
    function __desctruct()
    {
        if (is_resource($this->_fp)) {
            $this->save();
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
        if (is_file(realpath($path))) {
            $this->file = realpath($path);
            return $this->file;
        } else {
            return false;
        }
    }

    /**
     * @return string  Canonical absolute path with filename to use
     * for storing data
     */
    function getPath()
    {
        return $this->file;
    }
}