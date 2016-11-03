<?php

namespace Psi\Bridge\ObjectAgent\Doctrine\PhpcrOdm\Tests\Functional;

use Doctrine\ODM\PHPCR\Query\Builder\AbstractNode;
use Doctrine\ODM\PHPCR\Query\Builder\ConstraintAndx;
use Doctrine\ODM\PHPCR\Query\Builder\ConstraintComparison;
use Doctrine\ODM\PHPCR\Query\Builder\ConstraintOrx;
use Psi\Bridge\ObjectAgent\Doctrine\PhpcrOdm\ExpressionVisitor;
use Psi\Bridge\ObjectAgent\Doctrine\PhpcrOdm\Tests\Functional\Model\Page;
use Psi\Component\ObjectAgent\Query\Query;

class ExpressionVisitorTest extends PhpcrOdmTestCase
{
    private $queryBuilder;

    public function setUp()
    {
        $container = $this->getContainer();
        $this->queryBuilder = $container->get('phpcr_odm')
            ->getRepository(Page::class)->createQueryBuilder('a');

        $this->visitor = new ExpressionVisitor($this->queryBuilder, 'a');
    }

    /**
     * It should visit all comparators.
     *
     * @dataProvider provideComparator
     */
    public function testComparator(string $type, string $expectedOperator)
    {
        $this->visitor->dispatch(Query::comparison($type, 'title', 42));
        $query = $this->queryBuilder;
        $children = $query->getChildrenOfType(AbstractNode::NT_WHERE);
        $children = $children[0]->getChildrenOfType(AbstractNode::NT_CONSTRAINT);
        $this->assertEquals($expectedOperator, $children[0]->getOperator());
    }

    public function provideComparator()
    {
        return [
            [
                'eq',
                'jcr.operator.equal.to',
            ],
            [
                'neq',
                'jcr.operator.not.equal.to',
            ],
            [
                'gt',
                'jcr.operator.greater.than',
            ],
            [
                'gte',
                'jcr.operator.greater.than.or.equal.to',
            ],
            [
                'lte',
                'jcr.operator.less.than.or.equal.to',
            ],
            [
                'lt',
                'jcr.operator.less.than',
            ],
        ];
    }

    /**
     * It should visit and composites.
     */
    public function testVisitCompositeAnd()
    {
        $this->visitor->dispatch(
            Query::composite('and', Query::comparison('eq', 'title', 42))
        );

        $query = $this->queryBuilder;
        $children = $query->getChildrenOfType(AbstractNode::NT_WHERE);
        $children = $children[0]->getChildrenOfType(AbstractNode::NT_CONSTRAINT);
        $this->assertInstanceOf(ConstraintAndx::class, $children[0]);
    }

    /**
     * It should visit or composites.
     */
    public function testVisitCompositeOr()
    {
        $this->visitor->dispatch(
            Query::composite('or', Query::comparison('eq', 'title', 42))
        );

        $query = $this->queryBuilder;
        $children = $query->getChildrenOfType(AbstractNode::NT_WHERE);
        $children = $children[0]->getChildrenOfType(AbstractNode::NT_CONSTRAINT);
        $this->assertInstanceOf(ConstraintOrx::class, $children[0]);
    }

    /**
     * Test nested composites.
     */
    public function testNestedComposites()
    {
        $this->visitor->dispatch(
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

        $query = $this->queryBuilder;
        $children = $query->getChildrenOfType(AbstractNode::NT_WHERE);
        $children = $children[0]->getChildrenOfType(AbstractNode::NT_CONSTRAINT);
        $this->assertInstanceOf(ConstraintOrx::class, $children[0]);
        $children = $children[0]->getChildrenOfType(AbstractNode::NT_CONSTRAINT);
        $this->assertInstanceOf(ConstraintAndx::class, $children[1]);
        $children = $children[1]->getChildrenOfType(AbstractNode::NT_CONSTRAINT);
        $this->assertInstanceOf(ConstraintComparison::class, $children[0]);
        $this->assertInstanceOf(ConstraintOrx::class, $children[1]);
        $children = $children[1]->getChildrenOfType(AbstractNode::NT_CONSTRAINT);
        $this->assertInstanceOf(ConstraintComparison::class, $children[0]);
        $children = $children[0]->getChildrenOfType(AbstractNode::NT_OPERAND_STATIC);
        $this->assertEquals(67, $children[0]->getValue());
    }
}
