<?php

declare(strict_types=1);

namespace Psi\Component\ObjectAgent\Tests\Unit\Query;

use Psi\Component\ObjectAgent\Query\Join;
use Psi\Component\ObjectAgent\Query\Query;

class QueryTest extends \PHPUnit_Framework_TestCase
{
    public function testQuery()
    {
        $query = Query::create(\stdClass::class, [
            'selects' => ['f.bar' => 'foo'],
            'joins' => [Query::join('f.foobar', 'b')],
            'criteria' => $expr = Query::composite('and',
                Query::comparison('eq', 'f.foo', 'bar'),
                Query::comparison('gt', 'f.price', 100),
                Query::composite('or',
                    Query::comparison('lte', 123, 12),
                    Query::comparison('eq', 12, 12)
                )
            ),
        ]);

        $this->assertInstanceOf(Query::class, $query);
        $this->assertEquals(\stdClass::class, $query->getClassFqn());
        $this->assertSame($expr, $query->getExpression());
        $this->assertCount(1, $query->getJoins());
        $this->assertContainsOnlyInstancesOf(Join::class, $query->getJoins());
        $this->assertEquals(['f.bar' => 'foo'], $query->getSelects());
    }

    /**
     * It should throw an exception if invalid query components are provided.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid query keys "asd", valid keys:
     */
    public function testInvalid()
    {
        Query::create(\stdClass::class, [
            'asd' => 'basd',
        ]);
    }
}
