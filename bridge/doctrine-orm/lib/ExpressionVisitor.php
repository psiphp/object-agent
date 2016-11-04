<?php

namespace Psi\Bridge\ObjectAgent\Doctrine\Orm;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Psi\Component\ObjectAgent\Query\Comparison;
use Psi\Component\ObjectAgent\Query\Composite;
use Psi\Component\ObjectAgent\Query\Expression;

/**
 * Walks a Doctrine\Commons\Expr object graph and builds up a PHPCR-ODM
 * query using the (fluent) PHPCR-ODM query builder.
 */
class ExpressionVisitor
{
    private $expressionFactory;
    private $sourceAlias;

    private $parameters = [];

    /**
     * @param QueryBuilder $expressionFactory
     */
    public function __construct(Expr $expressionFactory, string $sourceAlias)
    {
        $this->expressionFactory = $expressionFactory;
        $this->sourceAlias = $sourceAlias;
    }

    /**
     * Walk the given expression to build up the PHPCR-ODM query builder.
     *
     * @param Expression $expr
     * @param AbstractNode|null $parentNode
     */
    public function dispatch(Expression $expr)
    {
        switch (true) {
            case $expr instanceof Comparison:
                return $this->walkComparison($expr);
                break;

            case $expr instanceof Composite:
                return $this->walkComposite($expr);
        }

        throw new \RuntimeException('Unknown Expression: ' . get_class($expr));
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * {@inheritdoc}
     */
    private function walkComparison(Comparison $comparison)
    {
        $field = $comparison->getField();
        $value = $comparison->getValue();

        switch ($comparison->getComparator()) {
            case Comparison::EQUALS:
                return $this->expressionFactory->eq($this->getField($field), $this->registerParameter($field, $value));

            case Comparison::NOT_EQUALS:
                return $this->expressionFactory->neq($this->getField($field), $this->registerParameter($field, $value));

            case Comparison::LESS_THAN:
                return $this->expressionFactory->lt($this->getField($field), $this->registerParameter($field, $value));

            case Comparison::LESS_THAN_EQUAL:
                return $this->expressionFactory->lte($this->getField($field), $this->registerParameter($field, $value));

            case Comparison::GREATER_THAN:
                return $this->expressionFactory->gt($this->getField($field), $this->registerParameter($field, $value));

            case Comparison::GREATER_THAN_EQUAL:
                return $this->expressionFactory->gte($this->getField($field), $this->registerParameter($field, $value));

            case Comparison::IN:
                return $this->expressionFactory->in($this->getField($field), $this->registerParameter($field, $value));

            case Comparison::NOT_IN:
                return $this->expressionFactory->notIn($this->getField($field), $this->registerParameter($field, $value));

            case Comparison::CONTAINS:
                return $this->expressionFactory->like($this->getField($field), $this->registerParameter($field, $value));

            case Comparison::NOT_CONTAINS:
                return $this->expressionFactory->notLike($this->getField($field), $this->registerParameter($field, $value));

            case Comparison::NULL:
                return $this->expressionFactory->isNull($this->getField($field), $this->registerParameter($field, $value));

            case Comparison::NOT_NULL:
                return $this->expressionFactory->isNotNull($this->getField($field), $this->registerParameter($field, $value));
        }

        throw new \RuntimeException('Unknown comparator: ' . $comparison->getComparator());
    }

    /**
     * {@inheritdoc}
     */
    private function walkComposite(Composite $expression)
    {
        $expressions = $expression->getExpressions();

        $ormExpressions = [];
        foreach ($expressions as $index => $childExpression) {
            $ormExpressions[] = $this->dispatch($childExpression);
        }

        $method = $expression->getType() == Composite::AND ? 'andX' : 'orX';

        return call_user_func_array([$this->expressionFactory, $method], $ormExpressions);
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

    private function registerParameter(string $name, $value)
    {
        $this->parameters[$name] = $value;

        return ':' . $name;
    }
}
