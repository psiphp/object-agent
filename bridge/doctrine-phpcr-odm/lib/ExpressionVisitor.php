<?php

namespace Psi\Bridge\ObjectAgent\Doctrine\PhpcrOdm;

use Doctrine\ODM\PHPCR\Query\Builder\AbstractNode;
use Doctrine\ODM\PHPCR\Query\Builder\QueryBuilder;
use Psi\Component\ObjectAgent\Query\Comparison;
use Psi\Component\ObjectAgent\Query\Composite;
use Psi\Component\ObjectAgent\Query\Expression;

class ExpressionVisitor
{
    private $queryBuilder;
    private $sourceAlias;

    /**
     * @param QueryBuilder $queryBuilder
     */
    public function __construct(QueryBuilder $queryBuilder, string $sourceAlias)
    {
        $this->queryBuilder = $queryBuilder;
        $this->sourceAlias = $sourceAlias;
    }

    /**
     * Walk the given expression to build up the PHPCR-ODM query builder.
     *
     * @param Expression $expr
     * @param AbstractNode|null $parentNode
     */
    public function dispatch(Expression $expr, AbstractNode $parentNode = null)
    {
        if ($parentNode === null) {
            $parentNode = $this->queryBuilder->where();
        }

        switch (true) {
            case $expr instanceof Comparison:
                return $this->walkComparison($expr, $parentNode);

            case $expr instanceof Composite:
                return $this->walkComposite($expr, $parentNode);
        }

        throw new \RuntimeException('Unknown Expression: ' . get_class($expr));
    }

    /**
     * {@inheritdoc}
     */
    private function walkComparison(Comparison $comparison, AbstractNode $parentNode)
    {
        $field = $comparison->getField();
        $value = $comparison->getValue();

        switch ($comparison->getComparator()) {
            case Comparison::EQUALS:
                return $parentNode->eq()->field($this->getField($field))->literal($value)->end();

            case Comparison::NOT_EQUALS:
                return $parentNode->neq()->field($this->getField($field))->literal($value)->end();

            case Comparison::LESS_THAN:
                return $parentNode->lt()->field($this->getField($field))->literal($value)->end();

            case Comparison::LESS_THAN_EQUAL:
                return $parentNode->lte()->field($this->getField($field))->literal($value)->end();

            case Comparison::GREATER_THAN:
                return $parentNode->gt()->field($this->getField($field))->literal($value)->end();

            case Comparison::GREATER_THAN_EQUAL:
                return $parentNode->gte()->field($this->getField($field))->literal($value)->end();

            case Comparison::IN:
                return $this->getInConstraint($parentNode, $field, $value);

            case Comparison::NOT_IN:
                $node = $parentNode->not();
                $this->getInConstraint($node, $field, $value);

                return $node->end();

            case Comparison::CONTAINS:
                return $parentNode->like()->field($this->getField($field))->literal($value)->end();

            case Comparison::NOT_CONTAINS:
                return $parentNode->not()->like()->field($this->getField($field))->literal($value)->end()->end();

            case Comparison::NULL:
                return $parentNode->not()->fieldIsset($this->getField($field))->end();

            case Comparison::NOT_NULL:
                return $parentNode->fieldIsset($this->getField($field));
        }

        throw new \RuntimeException('Unknown comparator: ' . $comparison->getComparator());
    }

    /**
     * {@inheritdoc}
     */
    private function walkComposite(Composite $expression, AbstractNode $parentNode)
    {
        $node = $expression->getType() === Composite::AND ? $parentNode->andX() : $parentNode->orX();

        $expressions = $expression->getExpressions();

        if (empty($expressions)) {
            return $node;
        }

        $leftExpression = array_shift($expressions);
        $this->dispatch($leftExpression, $node);

        $parentNode = $node;
        foreach ($expressions as $index => $expression) {
            if (count($expressions) === $index + 1) {
                $this->dispatch($expression, $parentNode);
                break;
            }

            $this->dispatch($expression, $parentNode);
        }

        return $node;
    }

    /**
     * @param string $field
     *
     * @return string
     */
    private function getField($field): string
    {
        return $this->sourceAlias . '.' . $field;
    }

    /**
     * @param AbstractNode $parentNode
     * @param string $field
     * @param array $values
     */
    private function getInConstraint(AbstractNode $parentNode, $field, array $values)
    {
        $orNode = $parentNode->orx();

        foreach ($values as $value) {
            $orNode->eq()->field($this->getField($field))->literal($value);
        }

        $orNode->end();
    }
}
