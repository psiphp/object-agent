<?php

declare(strict_types=1);

namespace Psi\Bridge\ObjectAgent\Doctrine\Orm;

use Doctrine\ORM\Query\Expr;
use Psi\Component\ObjectAgent\Query\Comparison;
use Psi\Component\ObjectAgent\Query\Composite;
use Psi\Component\ObjectAgent\Query\Expression;

class ExpressionVisitor
{
    private $expressionFactory;
    private $sourceAlias;

    private $parameters = [];

    public function __construct(Expr $expressionFactory, string $sourceAlias)
    {
        $this->expressionFactory = $expressionFactory;
        $this->sourceAlias = $sourceAlias;
    }

    /**
     * Walk the given expression to build up the ORM query builder.
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

    private function getField($field): string
    {
        // if a source alias has been used, then use that
        if (false !== strpos($field, '.')) {
            return $field;
        }

        // otherwise use the primary source alias.
        return $this->sourceAlias . '.' . $field;
    }

    private function registerParameter(string $name, $value)
    {
        // parameter tokens are named after the field name, which may be prefixed
        // with a source selector, replace "." with "_"
        $name = str_replace('.', '_', $name);

        $this->parameters[$name] = $value;

        return ':' . $name;
    }
}
