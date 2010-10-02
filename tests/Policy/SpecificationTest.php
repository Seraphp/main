<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */

require_once 'Policy/FieldEqualSpecification.class.php';
require_once 'Policy/FieldGreaterSpecification.class.php';
require_once 'Policy/FieldMatchesSpecification.class.php';
require_once 'Policy/OrSpecification.class.php';
require_once 'Policy/AndSpecification.class.php';
require_once 'Policy/NotSpecification.class.php';
/**
 * Class documentation
 */
class SpecificationTest extends PHPUnit_Framework_TestCase
{
    protected $_src;

    function setUp()
    {
        $this->_src = (object) array(
            'writer' => 'Douglas Adams',
            'numField' => 42,
            'title' => 'Hichhikers guide to the Galaxy'
        );
    }

    function testFieldEqualSpecification()
    {
        $writenByDA = new FieldEqualSpecification('writer', 'Douglas Adams');
        $this->assertTrue($writenByDA->isSatisfiedBy($this->_src));
        $not = new FieldEqualSpecification('numField', 24);
        $this->assertFalse($not->isSatisfiedBy($this->_src));
    }

    function testFieldGreaterSpecification()
    {
        $more = new FieldGreaterSpecification('numField', 43);
        $this->assertFalse($more->isSatisfiedBy($this->_src));
        $more = new FieldGreaterSpecification('numField', 40);
        $this->assertTrue($more->isSatisfiedBy($this->_src));
    }

    function testFieldMatchSpecification()
    {
        $digits = new FieldMatchesSpecification('numField', '/\d{2}/');
        $this->assertTrue($digits->isSatisfiedBy($this->_src));
        $noDigits = new FieldMatchesSpecification('writer', '/\d{2}/');
        $this->assertFalse($noDigits->isSatisfiedBy($this->_src));
    }

    /**
     * @dataProvider orTrueTable
     */
    function testOrSpecTrue($h, $l)
    {
        $orSpec = new OrSpecification($h, $l);
        $this->assertTrue($orSpec->isSatisfiedBy($this->_src));
    }

    /**
     * @dataProvider orFalseTable
     */
    function testOrSpecFalse($h, $l)
    {
        $orSpec = new OrSpecification($h, $l);
        $this->assertFalse($orSpec->isSatisfiedBy($this->_src));
    }

    /**
     * @dataProvider andTrueTable
     */
    function testAndSpecTrue($h, $l)
    {
        $andSpec = new AndSpecification($h, $l);
        $this->assertTrue($andSpec->isSatisfiedBy($this->_src));
    }

    /**
     * @dataProvider andFalseTable
     */
    function testAndSpecFalse($h, $l)
    {
        $andSpec = new AndSpecification($h, $l);
        $this->assertFalse($andSpec->isSatisfiedBy($this->_src));
    }

    function testNotSpecTrue()
    {
        $notSpec = new NotSpecification(
            new FieldEqualSpecification('numField', 24)
        );
        $this->assertTrue($notSpec->isSatisfiedBy($this->_src));
    }

    function testNotSpecFalse()
    {
        $notSpec = new NotSpecification(
            new FieldEqualSpecification('numField', 42)
        );
        $this->assertFalse($notSpec->isSatisfiedBy($this->_src));
    }

    function orTrueTable()
    {
        return array(
            array(
                new FieldEqualSpecification('numField', 24),
                new FieldEqualSpecification('writer', 'Douglas Adams')
            ),
            array(
                new FieldEqualSpecification('writer', 'Douglas Adams'),
                new FieldEqualSpecification('numField', 24)
            ),
            array(
                new FieldEqualSpecification('writer', 'Douglas Adams'),
                new FieldEqualSpecification('numField', 42)
            )
        );
    }

    function orFalseTable()
    {
        return array(
            array(
                new FieldEqualSpecification('numField', 24),
                new FieldEqualSpecification('writer', 'Peter Nagy')
            )
        );
    }

    function andTrueTable()
    {
        return array(
            array(
                new FieldEqualSpecification('writer', 'Douglas Adams'),
                new FieldEqualSpecification('numField', 42)
            )
        );
    }

    function andFalseTable()
    {
        return array(
            array(
                new FieldEqualSpecification('numField', 24),
                new FieldEqualSpecification('writer', 'Peter Nagy')
            ),
            array(
                new FieldEqualSpecification('writer', 'Douglas Adams'),
                new FieldEqualSpecification('numField', 24)
            ),
            array(
                new FieldEqualSpecification('writer', 'Peter Nagy'),
                new FieldEqualSpecification('numField', 42)
            )
        );
    }

}