<?php
/**
 * Contains implementation of NotSpecification class
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @copyright Copyright (c) 2008, Peter Nagy
 * @package Policy
 * @filesource
 */
/***/
namespace Seraphp\Policy;
require_once 'Policy/Specification.interface.php';
/**
 * Implements "Not" type specification policy
 *
 * @package Policy
 */
class NotSpecification implements Specification
{
    /**
     * @var Specification
     */
    protected $_spec;

    /**
     * Stores the specification which will be negated
     *
     * @param Specification $spec
     */
    public function __construct(Specification $spec)
    {
        $this->_spec = $spec;
    }

    /**
     * Returns negated result of coupled specification
     */
    public function isSatisfiedBy($src)
    {
        return (!$this->_spec->isSatisfiedBy($src));
    }

}