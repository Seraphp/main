<?php
/**
 * Holds implementation of singleton Registry class
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @copyright Copyright (c) 2008, Peter Nagy
 * @package Server
 * @subpackage Registry
 * @filesource
 */
/***/
namespace Seraphp\Server\Registry;
require_once 'Singleton.interface.php';
require_once 'Server/Registry/DataStore.class.php';
/**
 * Register class with nested keys
 *
 * Singleton implementation of the Registry pattern for storing and
 * retrieving values with keys.
 *
 * @package Server
 * @subpackage Registry
 */
class Registry extends \Seraphp\Server\Registry\DataStore
    implements \Seraphp\Singleton
{

    /**
     * Holds self reference
     *
     * @var Registry
     */
    static private $_instance = null;

    private function __construct(StoreEngine $engine = null)
    {
        if (isset($engine)) {
            $this->setEngine($engine);
        }
    }

    /**
     * Disabled cloning facility to preserve only 1 instance
     * @throws Exception  if used
     */
    public function __clone()
    {
        throw new \Exception('Cloning is disabled!');
    }

    /**
     * Returns instance of class
     *
     * @return self
     */
    public function getInstance(StoreEngine $engine = null)
    {
        if (self::$_instance === null ||
            get_class($engine) !== get_class(self::$_instance->_engine)) {
            self::$_instance = new self($engine);
        }
        return self::$_instance;
    }
}