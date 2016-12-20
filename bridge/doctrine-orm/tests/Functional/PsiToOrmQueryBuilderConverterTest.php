<?php

declare(strict_types=1);

namespace Psi\Bridge\ObjectAgent\Doctrine\Orm\Tests\Functional;

use Doctrine\ORM\Query\Expr\Comparison;
use Psi\Bridge\ObjectAgent\Doctrine\Orm\PsiToOrmQueryBuilderConverter;
use Psi\Component\ObjectAgent\Query\Expression;
use Psi\Component\ObjectAgent\Query\Query;
use Psi\Component\ObjectAgent\Tests\Functional\Model\Page;

class PsiToOrmQueryBuilderConverterTest extends OrmTestCase
{
    /**
     * @var PsiToOrmQueryBuilderConverter
     */
    private $converter;

    public function setUp()
    {
        $container = $this->getContainer();

        $this->converter = new PsiToOrmQueryBuilderConverter($container->get('entity_manager'));
    }

    /**
     * @dataProvider provideExpression
     */
    public function testExpression(Expression $expression, string $expectedQuery)
    {
        $query = Query::create(Page::class, [
            'criteria' => $expression,
        ]);
        $queryBuilder = $this->converter->convert($query);

        $expectedQuery = sprintf(
            'SELECT a FROM Psi\Component\ObjectAgent\Tests\Functional\Model\Page a WHERE %s',
            $expectedQuery
        );

        $this->assertEquals($expectedQuery, $queryBuilder->getDql());
    }

    public function provideExpression()
    {
        return [
            [
                Query::comparison('eq', 'title', 42),
                'a.title = :param0',
            ],
            [
                Query::comparison('neq', 'title', 42),
                'a.title <> :param0',
            ],
            [
                Query::comparison('gt', 'title', 42),
                'a.title > :param0',
            ],
            [
                Query::comparison('gte', 'title', 42),
                'a.title >= :param0',
            ],
            [
                Query::comparison('lte', 'title', 42),
                'a.title <= :param0',
            ],
            [
                Query::comparison('lt', 'title', 42),
                'a.title < :param0',
            ],
            [
                Query::comparison('contains', 'title', 42),
                'a.title LIKE :param0',
            ],
            [
                Query::comparison('not_contains', 'title', 42),
                'a.title NOT LIKE :param0',
            ],
            [
                Query::comparison('null', 'title'),
                'a.title IS NULL',
            ],
            [
                Query::comparison('not_null', 'title'),
                'a.title IS NOT NULL',
            ],
            [
                Query::comparison('in', 'title', 42),
                'a.title IN(:param0)',
            ],
            [
                Query::comparison('nin', 'title', 42),
                'a.title NOT IN(:param0)',
            ],
            [
                Query::composite('and',
                    Query::comparison('eq', 'title', 42),
                    Query::comparison('eq', 'foobar', 'barfoo')
                ),
                'a.title = :param0 AND a.foobar = :param1',
            ],
            [
                Query::composite('or',
                    Query::comparison('eq', 'title', 42),
                    Query::comparison('eq', 'foobar', 'barfoo')
                ),
                'a.title = :param0 OR a.foobar = :param1',
            ],
            [
                Query::comparison('nin', 'p.title', 42),
                'p.title NOT IN(:param0)',
            ],
            [
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
                ),
                'a.title = :param0 OR (a.title = :param1 AND a.title = :param2)',
            ],
            'it should allow empty composites' => [
                Query::composite('and'),
                '', // and that works??
            ],
        ];
    }
}
