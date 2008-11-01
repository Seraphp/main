<?php
/**
 * Contains implementation of NotSpecification class
 * 
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
//namespace Phaser::Policy;
require_once 'Policy/Specification.interface.php';
/**
 * Implements "Not" type specification policy
 * 
 * @package Phaser
 * @subpackage Policy
 */
class NotSpecification implements Specification{
    /**
     * @var Specification
     */
    protected $spec;
    
    /**
     * Stores the specification which will be negated
     *
     * @param Specification $spec
     */
    public function __construct(Specification $spec){
        $this->spec = $spec;
    }
    
    /**
     * Returns negated result of coupled specification
     */
    public function isSatisfiedBy($src){
        return (!$this->spec->isSatisfiedBy($src));
    }

}
?>