<?php
/**
 * Contains implementation of FieldEqualSpecification class
 * 
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
//namespace Phaser\Policy;
require_once 'Policy/FieldSpecification.class.php';
/**
 * Implements 'equal fields' policy.
 *  
 * @package Policy
 */
class FieldEqualSpecification extends FieldSpecification
{
    /**
     * @var mixed
     */
    protected $value;

    /**
     * Stores field and value for later use
     *
     * @param string $field
     * @param mixed $value
     */
    public function __construct($field, $value){
        $this->value = $value;
        parent::__construct($field);
    }

    /**
     * Returns true if value is logicaly equal (==) with field's value 
     */
    public function isSatisfiedBy($src){
        return ($src->{$this->field} == $this->value);
    }
    
}
?>