<?php
/**
 * Contains implementation of FieldMathcesSpecification class
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @copyright Copyright (c) 2008, Peter Nagy
 * @package Policy
 * @filesource
 */
/***/
namespace Seraphp\Policy;
require_once 'Policy/FieldSpecification.class.php';
/**
 * Implements 'field matches with value' policy.
 *
 * Implementation is based on PHP's preg_match function
 *
 * @package Policy
 */
class FieldMatchesSpecification extends FieldSpecification
{
    /**
     * Stores the pattern string with delimiters
     *
     * @var string
     */
    protected $_pattern = '';

    public function __construct($field, $pattern)
    {
        $this->_pattern = $pattern;
        parent::__construct($field);
    }

    /**
     * Returns true if given pattern result in minimum 1 match with field's
     * value.
     *
     * Pattern should contains delimiters also, like: /\d{2}/
     */
    public function isSatisfiedBy($src)
    {
        return ( preg_match($this->_pattern, $src->{$this->_field}) == 1 );
    }
}