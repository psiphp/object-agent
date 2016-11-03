<?php

namespace Psi\Component\ObjectAgent\Tests\Unit\Query;

use Psi\Component\ObjectAgent\Query\Query;

class QueryTest extends \PHPUnit_Framework_TestCase
{
    public function testQuery()
    {
        $query = Query::create(\stdClass::class, $expr = Query::composite('and',
            Query::comparison('eq', 'foo', 'bar'),
            Query::comparison('gt', 'price', 100),
            Query::composite('or',
                Query::comparison('lte', 123, 12),
                Query::comparison('eq', 12, 12)
            )
        ));

        $this->assertInstanceOf(Query::class, $query);
        $this->assertEquals(\stdClass::class, $query->getClassFqn());
        $this->assertSame($expr, $query->getExpression());
    }
}
