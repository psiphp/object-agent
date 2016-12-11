<?php

declare(strict_types=1);

namespace Psi\Bridge\ObjectAgent\Doctrine\Collections\Tests\Functional;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Common\Collections\Expr\CompositeExpression;
use Psi\Bridge\ObjectAgent\Doctrine\Collections\CollectionsVisitor;
use Psi\Component\ObjectAgent\Query\Query;

class CollectionsVisitorTest extends \PHPUnit_Framework_TestCase
{
    private $visitor;

    public function setUp()
    {
        $this->visitor = new CollectionsVisitor(Criteria::expr());
    }

    /**
     * It should visit all comparators.
     *
     * @dataProvider provideComparator
     */
    public function testComparator(string $type, string $expectedOperator)
    {
        $expr = $this->visitor->dispatch(Query::comparison($type, 'title', 42));
        $this->assertInstanceOf(Comparison::class, $expr);
        $this->assertEquals($expectedOperator, $expr->getOperator());
        $this->assertEquals('title', $expr->getField());
        $this->assertEquals(42, $expr->getValue()->getValue());
    }

    public function provideComparator()
    {
        return [
            [
                'eq',
                '=',
            ],
            [
                'neq',
                '<>',
            ],
            [
                'gt',
                '>',
            ],
            [
                'gte',
                '>=',
            ],
            [
                'lte',
                '<=',
            ],
            [
                'lt',
                '<',
            ],
            [
                'contains',
                'CONTAINS',
            ],
        ];
    }

    /**
     * It should throw an exception on unsupported comparators.
     *
     * @dataProvider provideUnsupportedComparators
     *
     * @expectedException Psi\Component\ObjectAgent\Exception\BadMethodCallException
     */
    public function testUnsupportedComparators($comparator)
    {
        $this->visitor->dispatch(Query::comparison($comparator, 'title', 42));
    }

    public function provideUnsupportedComparators()
    {
        return [
            [
                'not_contains',
            ],
            [
                'not_null',
            ],
        ];
    }

    /**
     * It should visit complex comparators.
     *
     * @dataProvider provideComplexComparator
     */
    public function testComplexComparator(string $type, $value, \Closure $assertion)
    {
        $expr = $this->visitor->dispatch(Query::comparison($type, 'title', $value));
        $assertion($expr);
    }

    public function provideComplexComparator()
    {
        return [
            [
                'null',
                null,
                function ($expr) {
                    $this->assertEquals('=', $expr->getOperator());
                    $this->assertNull($expr->getValue()->getValue());
                },
            ],
            [
                'in',
                [1, 2, 3],
                function ($expr) {
                    $this->assertEquals('IN', $expr->getOperator());
                    $this->assertEquals([1, 2, 3], $expr->getValue()->getValue());
                },
            ],
            [
                'nin',
                [1, 2, 3],
                function ($expr) {
                    $this->assertEquals('NIN', $expr->getOperator());
                    $this->assertEquals([1, 2, 3], $expr->getValue()->getValue());
                },
            ],
        ];
    }

    /**
     * It should visit and composites.
     */
    public function testVisitCompositeAnd()
    {
        $expr = $this->visitor->dispatch(
            Query::composite('and', Query::comparison('eq', 'title', 42))
        );

        $this->assertInstanceOf(CompositeExpression::class, $expr);
        $this->assertEquals('AND', $expr->getType());
        $this->assertInstanceOf(Comparison::class, $expr->getExpressionList()[0]);
    }

    /**
     * It should visit or composites.
     */
    public function testVisitCompositeOr()
    {
        $expr = $this->visitor->dispatch(
            Query::composite('or', Query::comparison('eq', 'title', 42))
        );

        $this->assertInstanceOf(CompositeExpression::class, $expr);
        $this->assertEquals('OR', $expr->getType());
        $this->assertInstanceOf(Comparison::class, $expr->getExpressionList()[0]);
    }
}
