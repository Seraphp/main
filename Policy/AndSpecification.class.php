<?php
/**
 * Contains implementation of AndSpecification class
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id:AndSpecification.class.php 234 2008-11-01 15:35:32Z peter $
 * @copyright Copyright (c) 2008, Peter Nagy
 * @package Policy
 * @filesource
 */
/***/
//namespace Seraphp\Policy;
require_once 'Policy/Specification.interface.php';
/**
 * Implements "And" type specification policy
 *
 * @package Policy
 */
class AndSpecification implements Specification
{

    /**
     * @var Specification
     */
    protected $_spec;
    /**
     * @var Specification
     */
    protected $_spec2;

    /**
     * Stores the specifications which will be related
     *
     * @param Specification $spec
     * @param Specification $spec2
     */
    public function __construct(Specification $spec, Specification $spec2)
    {
        $this->_spec = $spec;
        $this->_spec2 = $spec2;
    }

    /**
     * Return true if the two specification are both satisfied
     */
    public function isSatisfiedBy($src)
    {
        return (
            $this->_spec->isSatisfiedBy($src)&&
            $this->_spec2->isSatisfiedBy($src)
        );
    }

}