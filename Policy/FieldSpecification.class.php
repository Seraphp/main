<?php
/**
 * Contains abstract parent class of all FieldPolicy implementations.
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id:FieldSpecification.class.php 234 2008-11-01 15:35:32Z peter $
 * @copyright Copyright (c) 2008, Peter Nagy
 * @package Policy
 * @filesource
 */
/***/
//namespace Seraphp\Policy;
require_once 'Policy/Specification.interface.php';
require_once 'Policy/AndSpecification.class.php';
require_once 'Policy/OrSpecification.class.php';
require_once 'Policy/NotSpecification.class.php';
/**
 * Examine a field in a datasource
 *
 * Data source should be any array or calss implementing Iterable interface.
 * It has methods to implement logical criteria to make it easier creating
 * complex policies. All its subclasses will get the same methods, so if any
 * new logical policy class are implemented, they should be added to this
 * class.
 *
 * @package Policy
 * @abstract
 */
abstract class FieldSpecification implements Specification{

    /**
     * @var string
     */
    protected $field='';

    /**
     * Stores field name for later use.
     *
     * @param string $field
     */
    public function __construct($field){
        $this->field = $field;
    }

    /**
     * Shortcut method for "And" type logical policy
     *
     * @param Specification $spec
     * @return AndSpecification
     */
    public function and_(Specification $spec){
        return new AndSpecification($this, $spec);
    }

    /**
     * Shortcut method for "Or" type logical policy
     *
     * @param Specification $spec
     * @return OrSpecification
     */
    public function or_(Specification $spec){
        return new OrSpecification($this, $spec);
    }

    /**
     * Shortcut method for "Not" type logical policy
     *
     * @param Specification $spec
     * @return NotSpecificationn
     */
    public function not_(Specification $spec){
        return new NotSpecification($spec);
    }
}
?>