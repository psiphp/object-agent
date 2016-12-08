<?php

declare(strict_types=1);

namespace Psi\Bridge\ObjectAgent\Doctrine\Collections;

use Doctrine\Common\Collections\ExpressionBuilder;
use Psi\Component\ObjectAgent\Exception\BadMethodCallException;
use Psi\Component\ObjectAgent\Query\Comparison;
use Psi\Component\ObjectAgent\Query\Composite;
use Psi\Component\ObjectAgent\Query\Expression;

class CollectionsVisitor
{
    private $expressionBuilder;

    public function __construct(ExpressionBuilder $expressionBuilder)
    {
        $this->expressionBuilder = $expressionBuilder;
    }

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

    private function walkComparison(Comparison $comparison)
    {
        $field = $comparison->getField();
        $value = $comparison->getValue();

        switch ($comparison->getComparator()) {
            case Comparison::EQUALS:
                return $this->expressionBuilder->eq($field, $value);

            case Comparison::NOT_EQUALS:
                return $this->expressionBuilder->neq($field, $value);

            case Comparison::LESS_THAN:
                return $this->expressionBuilder->lt($field, $value);

            case Comparison::LESS_THAN_EQUAL:
                return $this->expressionBuilder->lte($field, $value);

            case Comparison::GREATER_THAN:
                return $this->expressionBuilder->gt($field, $value);

            case Comparison::GREATER_THAN_EQUAL:
                return $this->expressionBuilder->gte($field, $value);

            case Comparison::IN:
                return $this->expressionBuilder->in($field, $value);

            case Comparison::NOT_IN:
                return $this->expressionBuilder->notIn($field, $value);

            case Comparison::CONTAINS:
                return $this->expressionBuilder->contains($field, $value);

            case Comparison::NOT_CONTAINS:
                throw BadMethodCallException::comparisonNotSupported(Comparison::NOT_CONTAINS);
            case Comparison::NULL:
                return $this->expressionBuilder->isNull($field, $value);

            case Comparison::NOT_NULL:
                throw BadMethodCallException::comparisonNotSupported(Comparison::NOT_NULL);
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

        return call_user_func_array([$this->expressionBuilder, $method], $ormExpressions);
    }
}
