<?php

namespace Psi\Component\ObjectAgent\Tests\Unit\Query;

use Psi\Component\ObjectAgent\Query\Comparison;

class ComparisonTest extends \PHPUnit_Framework_TestCase
{
    private $comparison;

    /**
     * It should throw an exception if an invalid operator is provided.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown comparator "foobar". Known comparators: "eq", "neq", "gt", "gte", "lt", "lte"
     */
    public function testInvalidOperator()
    {
        new Comparison('foobar', 1, 2);
    }

    /**
     * It should accept valid operators.
     * It should provide accessors to the values.
     */
    public function testValidComparators()
    {
        $validComparators = ['eq', 'neq', 'gt', 'gte', 'lt', 'lte'];

        foreach ($validComparators as $comparator) {
            $comparison = new Comparison($comparator, 12, 24);
            $this->assertEquals($comparator, $comparison->getComparator());
            $this->assertEquals(12, $comparison->getField());
            $this->assertEquals(24, $comparison->getValue());
        }
    }
}
