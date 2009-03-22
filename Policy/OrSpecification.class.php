<?php
/**
 * Contains implementation of OrSpecification class
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @copyright Copyright (c) 2008, Peter Nagy
 * @package Policy
 * @filesource
 */
/***/
//namespace Seraphp\Policy;
require_once 'Policy/Specification.interface.php';
/**
 * Implements "Or" type specification policy
 *
 * @package Policy
 */
class OrSpecification implements Specification
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
     * Return true if one of the two specification is satisfied
     */
    public function isSatisfiedBy($src)
    {
        return (
            $this->_spec->isSatisfiedBy($src)||
            $this->_spec2->isSatisfiedBy($src)
        );
    }

}
?>