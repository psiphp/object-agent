<?php

namespace Psi\Component\ObjectAgent\Tests\Unit\Query\Converter;

use Psi\Component\ObjectAgent\Query\Converter\ArrayConverter;
use Psi\Component\ObjectAgent\Query\Query;
use Psi\Component\ObjectAgent\Query\Composite;
use Psi\Component\ObjectAgent\Query\Comparison;
use Psi\Component\ObjectAgent\Query\Expression;
use Psi\Component\ObjectAgent\Query\Join;

class ArrayConverterTest extends \PHPUnit_Framework_TestCase

{
    /**
     * It should produce a query.
     */
    public function testQuery()
    {
        $orderings = [ 'foo' => 'asc' ];
        $selects = [ 'a.foobar' => 'foobar' ];
        $query = (new ArrayConverter())->__invoke([
            'from' => \stdClass::class,
            'joins' => [ [ "join" => "f.foobar", "alias" => "f" ] ],
            'selects' => $selects,
            'orderings' => $orderings,
            'firstResult' => 5,
            'maxResults' => 10,
        ]);

        $this->assertEquals($selects, $query->getSelects());
        $this->assertEquals($orderings, $query->getOrderings());
        $this->assertEquals(5, $query->getFirstResult());
        $this->assertEquals(10, $query->getMaxResults());

        $this->assertEquals([
            new Join('f.foobar', 'f')
        ], $query->getJoins());
    }

    /**
     * It should throw an exception if no "from" is provided.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage specify the "from"
     */
    public function testNoFrom()
    {
        (new ArrayConverter())->__invoke([
        ]);
    }

    /**
     * It should throw an exception if comparison does not have an array as a value.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Comparison must have an array as the right-sided value
     */
    public function testComparisonNoArrayValue()
    {
        (new ArrayConverter())->__invoke([
            'from' => \stdClass::class,
            'criteria' => [ 'eq' => 'foo',]
        ]);
    }

    /**
     * It should throw an exception if an invalid operator is encountered.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown expression operator 
     */
    public function testThrowExceptionInvalidOperator()
    {
        (new ArrayConverter())->__invoke([
            'from' => \stdClass::class,
            'criteria' => [ 'and' => [ 'eq' => [ 'foo' => 'bar' ]], 'or' => [ 'eq' => [ 'boo' => 'baz' ]], 'asd' => [] ],
        ]);
    }

    /**
     * @dataProvider provideConverter
     */
    public function testConverter(array $array, Expression $expected)
    {
        $query = (new ArrayConverter())->__invoke(['from' => \stdClass::class, 'criteria' => $array]);
        $this->assertEquals($expected, $query->getExpression());
    }

    public function provideConverter()
    {
        return [
            [
                [
                    'and' => [
                        'eq' => [ 'foo' => 'bar' ],
                        'lt' => [ 'foo' => 62 ],
                    ]
                ],
                Query::composite(
                    Composite::AND,
                    Query::composite(
                        Composite::AND,
                        Query::comparison(Comparison::EQUALS, 'foo', 'bar'),
                        Query::comparison(Comparison::LESS_THAN, 'foo', 62)
                    )
                )
            ],
            [
                [
                    'or' => [
                        'eq' => [ 'foo' => 'bar' ],
                    ]
                ],
                Query::composite(
                    Composite::AND,
                    Query::composite(
                        Composite::OR,
                        Query::comparison(Comparison::EQUALS, 'foo', 'bar')
                    )
                )
            ],
            [
                [
                    'eq' => [ 'foo' => 'bar' ],
                ],
                Query::composite(
                    Composite::AND,
                    Query::comparison(Comparison::EQUALS, 'foo', 'bar')
                )
            ],
            [
                [
                    'eq' => [ 'foo' => 'bar', 'bar' => 'bar', 'zar' => 'bar' ],
                ],
                Query::composite(
                    Composite::AND,
                    Query::comparison(Comparison::EQUALS, 'foo', 'bar'),
                    Query::comparison(Comparison::EQUALS, 'bar', 'bar'),
                    Query::comparison(Comparison::EQUALS, 'zar', 'bar')
                )
            ],
        ];
    }
}
