<?php
/**
 * Holds NestedException implementation
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id: LogException.class.php 703 2009-11-05 09:18:34Z peter $
 * @copyright Copyright (c) 2010, Peter Nagy
 * @package Exceptions
 * @filesource
 */
/**
 * NestedException class
 *
 * @package Exceptions
 */
class NestedException extends Exception
{
    protected $_priorException;

    public function __construct($message, $code = null,
        Exception $previous = null)
    {
        $this->_priorException = $previous;
        parent::__construct($message, $code);
    }

    public function getPrior()
    {
        return $this->_priorException;
    }

    public function setPrior(Exception $previous)
    {
        $this->_priorException = $previous;
    }
}