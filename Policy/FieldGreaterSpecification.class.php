<?php
/**
 * Contains FieldGreaterSpecification policy class
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @copyright Copyright (c) 2008, Peter Nagy
 * @package Policy
 * @filesource
 */
/***/
//namespace Seraphp\Policy;
require_once 'Policy/FieldSpecification.class.php';
/**
 * Implements 'field greater than value' policy.
 *
 * @package Policy
 */
class FieldGreaterSpecification extends FieldSpecification{

    /**
     * @var mixed
     */
    protected $value;

    public function __construct($field, $value){
        $this->value = $value;
        parent::__construct($field);
    }

    /**
     * Returns true if datasource's field is bigger than the given value
     */
    public function isSatisfiedBy($src){
        return ($src->{$this->field} > $this->value);
    }
}
?>