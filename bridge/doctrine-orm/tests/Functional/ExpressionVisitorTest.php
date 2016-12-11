<?php

declare(strict_types=1);

namespace Psi\Bridge\ObjectAgent\Doctrine\Orm\Tests\Functional;

use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Expr\Func;
use Doctrine\ORM\Query\Expr\Orx;
use Psi\Bridge\ObjectAgent\Doctrine\Orm\ExpressionVisitor;
use Psi\Component\ObjectAgent\Query\Query;
use Psi\Component\ObjectAgent\Tests\Functional\Model\Page;

class ExpressionVisitorTest extends OrmTestCase
{
    private $queryBuilder;

    public function setUp()
    {
        $container = $this->getContainer();
        $this->queryBuilder = $container->get('entity_manager')
            ->getRepository(Page::class)->createQueryBuilder('a');

        $this->visitor = new ExpressionVisitor($this->queryBuilder->expr(), 'a');
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
        $this->assertEquals('a.title', $expr->getLeftExpr());
        $this->assertEquals(':title', $expr->getRightExpr());
        $params = $this->visitor->getParameters();
        $this->assertEquals(42, $params['title']);
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
                'LIKE',
            ],
            [
                'not_contains',
                'NOT LIKE',
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
                    $this->assertEquals('a.title IS NULL', $expr);
                },
            ],
            [
                'not_null',
                null,
                function ($expr) {
                    $this->assertEquals('a.title IS NOT NULL', $expr);
                },
            ],
            [
                'in',
                [1, 2, 3],
                function ($expr) {
                    $this->assertInstanceOf(Func::class, $expr);
                    $this->assertEquals('a.title IN', $expr->getName());
                },
            ],
            [
                'nin',
                [1, 2, 3],
                function ($expr) {
                    $this->assertInstanceOf(Func::class, $expr);
                    $this->assertEquals('a.title NOT IN', $expr->getName());
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

        $this->assertInstanceOf(Andx::class, $expr);
        $this->assertInstanceOf(Comparison::class, $expr->getParts()[0]);
    }

    /**
     * It should visit or composites.
     */
    public function testVisitCompositeOr()
    {
        $expr = $this->visitor->dispatch(
            Query::composite('or', Query::comparison('eq', 'title', 42))
        );

        $this->assertInstanceOf(Orx::class, $expr);
        $this->assertInstanceOf(Comparison::class, $expr->getParts()[0]);
    }

    /**
     * Test nested composites.
     */
    public function testNestedComposites()
    {
        $expr = $this->visitor->dispatch(
            Query::composite(
                'or',
                Query::comparison('eq', 'title', 42),
                QUery::composite(
                    'and',
                    Query::comparison('eq', 'title', 'boobar'),
                    Query::composite(
                        'or',
                        Query::comparison('eq', 'title', 67)
                    )
                )
            )
        );
        $this->assertInstanceOf(Orx::class, $expr);
        $this->assertInstanceOf(Comparison::class, $expr->getParts()[0]);
        $this->assertInstanceOf(Andx::class, $expr->getParts()[1]);
        $parts = $expr->getParts()[1]->getParts();
        $this->assertInstanceOf(Comparison::class, $parts[0]);
        $this->assertInstanceOf(Orx::class, $parts[1]);
    }

    /**
     * It should process "empty" composites.
     */
    public function testVisitEmptyComposite()
    {
        $expr = $this->visitor->dispatch(
            Query::composite('and')
        );

        $this->assertInstanceOf(Andx::class, $expr);
        $this->assertCount(0, $expr->getParts());
    }
}
