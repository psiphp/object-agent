<?php

declare(strict_types=1);

namespace Psi\Bridge\ObjectAgent\Doctrine\Orm;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Psi\Component\ObjectAgent\Query\Comparison;
use Psi\Component\ObjectAgent\Query\Composite;
use Psi\Component\ObjectAgent\Query\Expression;
use Psi\Component\ObjectAgent\Query\Join;
use Psi\Component\ObjectAgent\Query\Query;

class PsiToOrmQueryBuilderConverter
{
    const FROM_ALIAS = 'a';

    private $paramIndex = 0;
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Convert the given Psi Query into a Doctrine ORM QueryBuilder instance.
     */
    public function convert(Query $query): QueryBuilder
    {
        $queryBuilder = $this->entityManager
            ->getRepository($query->getClassFqn())
            ->createQueryBuilder(self::FROM_ALIAS);

        $this->buildSelects($queryBuilder, $query);
        $this->buildJoins($queryBuilder, $query);

        if ($query->hasExpression()) {
            $this->buildWhere($queryBuilder, $query);
        }

        $this->buildOrderings($queryBuilder, $query);
        $this->buildLimit($queryBuilder, $query);

        return $queryBuilder;
    }

    private function buildSelects(QueryBuilder $queryBuilder, Query $query)
    {
        $selects = [];
        foreach ($query->getSelects() as $selectName => $selectAlias) {
            $select = $selectName . ' ' . $selectAlias;

            // if the "index" is numeric, then assume that the value is the
            // name and that no alias is being used.
            if (is_int($selectName)) {
                $select = $selectAlias;
            }

            $selects[] = $select;
        }

        if (empty($selects)) {
            return;
        }

        $queryBuilder->select($selects);
    }

    private function buildJoins(QueryBuilder $queryBuilder, Query $query)
    {
        foreach ($query->getJoins() as $join) {
            switch ($join->getType()) {
                case Join::INNER_JOIN:
                    $queryBuilder->innerJoin($join->getJoin(), $join->getAlias());
                    continue 2;
                case Join::LEFT_JOIN:
                    $queryBuilder->leftJoin($join->getJoin(), $join->getAlias());
                    continue 2;
            }

            throw new \InvalidArgumentException(sprintf(
                'Do not know what to do with join of type "%s"', $join->getType()
            ));
        }
    }

    private function buildWhere(QueryBuilder $queryBuilder, Query $query)
    {
        $expr = $this->walkExpression($queryBuilder, $query->getExpression());
        $queryBuilder->where($expr);
    }

    private function buildOrderings(QUeryBuilder $queryBuilder, Query $query)
    {
        foreach ($query->getOrderings() as $field => $order) {
            $queryBuilder->addOrderBy($this->getField($field), $order);
        }
    }

    private function buildLimit(QueryBuilder $queryBuilder, Query $query)
    {
        if (null !== $query->getFirstResult()) {
            $queryBuilder->setFirstResult($query->getFirstResult());
        }

        if (null !== $query->getMaxResults()) {
            $queryBuilder->setMaxResults($query->getMaxResults());
        }
    }

    private function walkExpression(QueryBuilder $queryBuilder, Expression $expr)
    {
        switch (true) {
            case $expr instanceof Comparison:
                return $this->walkComparison($queryBuilder, $expr);
                break;

            case $expr instanceof Composite:
                return $this->walkComposite($queryBuilder, $expr);
        }

        throw new \RuntimeException(sprintf(
            'Unknown Expression: %s',
            get_class($expr)
        ));
    }

    private function walkComparison(QueryBuilder $queryBuilder, Comparison $comparison)
    {
        $expressionFactory = $queryBuilder->expr();

        $field = $comparison->getField();
        $value = $comparison->getValue();

        switch ($comparison->getComparator()) {
            case Comparison::EQUALS:
                return $expressionFactory->eq($this->getField($field), $this->registerParameter($queryBuilder, $value));

            case Comparison::NOT_EQUALS:
                return $expressionFactory->neq($this->getField($field), $this->registerParameter($queryBuilder, $value));

            case Comparison::LESS_THAN:
                return $expressionFactory->lt($this->getField($field), $this->registerParameter($queryBuilder, $value));

            case Comparison::LESS_THAN_EQUAL:
                return $expressionFactory->lte($this->getField($field), $this->registerParameter($queryBuilder, $value));

            case Comparison::GREATER_THAN:
                return $expressionFactory->gt($this->getField($field), $this->registerParameter($queryBuilder, $value));

            case Comparison::GREATER_THAN_EQUAL:
                return $expressionFactory->gte($this->getField($field), $this->registerParameter($queryBuilder, $value));

            case Comparison::IN:
                return $expressionFactory->in($this->getField($field), $this->registerParameter($queryBuilder, $value));

            case Comparison::NOT_IN:
                return $expressionFactory->notIn($this->getField($field), $this->registerParameter($queryBuilder, $value));

            case Comparison::CONTAINS:
                return $expressionFactory->like($this->getField($field), $this->registerParameter($queryBuilder, $value));

            case Comparison::NOT_CONTAINS:
                return $expressionFactory->notLike($this->getField($field), $this->registerParameter($queryBuilder, $value));

            case Comparison::NULL:
                return $expressionFactory->isNull($this->getField($field));

            case Comparison::NOT_NULL:
                return $expressionFactory->isNotNull($this->getField($field));
        }

        throw new \RuntimeException('Unknown comparator: ' . $comparison->getComparator());
    }

    private function walkComposite(QueryBuilder $queryBuilder, Composite $expression)
    {
        $expressions = $expression->getExpressions();

        $ormExpressions = [];
        foreach ($expressions as $index => $childExpression) {
            $ormExpressions[] = $this->walkExpression($queryBuilder, $childExpression);
        }

        $method = $expression->getType() == Composite::AND ? 'andX' : 'orX';

        return call_user_func_array([$queryBuilder->expr(), $method], $ormExpressions);
    }

    private function getField($field): string
    {
        // if a source alias has been used, then use that
        if (false !== strpos($field, '.')) {
            return $field;
        }

        // otherwise use the primary source alias.
        return self::FROM_ALIAS . '.' . $field;
    }

    private function registerParameter(QueryBuilder $queryBuilder, $value)
    {
        $name = 'param' . $this->paramIndex++;
        $queryBuilder->setParameter($name, $value);

        return ':' . $name;
    }
}
