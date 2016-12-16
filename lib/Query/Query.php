<?php

declare(strict_types=1);

namespace Psi\Component\ObjectAgent\Query;

use Psi\Component\ObjectAgent\Query\Expression;
use Psi\Component\ObjectAgent\Query\Having;

final class Query
{
    private $classFqn;
    private $expression;
    private $orderings;
    private $firstResult;
    private $maxResults;
    private $joins;
    private $selects;
    private $having;
    private $groupBys;

    private function __construct(
        string $classFqn,
        array $selects = [],
        array $joins = [],
        Expression $expression = null,
        Having $having = null,
        array $groupBys = [],
        array $orderings = [],
        int $firstResult = null,
        int $maxResults = null
    ) {
        $this->classFqn = $classFqn;
        $this->expression = $expression;
        $this->orderings = $orderings;
        $this->firstResult = $firstResult;
        $this->maxResults = $maxResults;
        $this->joins = $joins;
        $this->groupBys = $groupBys;
        $this->selects = $selects;
        $this->having = $having;

        array_walk($joins, function (Join $join) {
        });
    }

    public static function create(
        string $classFqn,
        array $query = []
    ) {
        $defaults = [
            'selects' => [],
            'criteria' => null,
            'orderings' => [],
            'joins' => [],
            'having' => null,
            'groupBys' => [],
            'firstResult' => null,
            'maxResults' => null,
        ];

        if ($diff = array_diff(array_keys($query), array_keys($defaults))) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid query keys "%s", valid keys: "%s"',
                implode('", "', $diff), implode('", "', array_keys($defaults))
            ));
        }

        $query = array_merge($defaults, $query);

        return new self(
            $classFqn,
            $query['selects'],
            $query['joins'],
            $query['criteria'],
            $query['having'],
            $query['groupBys'],
            $query['orderings'],
            $query['firstResult'],
            $query['maxResults']
        );
    }

    public static function comparison(string $comparator, $value1, $value2): Comparison
    {
        return new Comparison($comparator, $value1, $value2);
    }

    public static function composite(string $type, ...$expressions): Composite
    {
        return new Composite($type, $expressions);
    }

    public static function join(string $type, string $alias)
    {
        return new Join($type, $alias);
    }

    public static function having(Expression $expression)
    {
        return new Having($expression);
    }

    public function cloneWith(array $parts)
    {
        return self::create(
            $this->getClassFqn(),
            [
                'selects' => array_key_exists('selects', $parts) ? $parts['selects'] : $this->selects,
                'criteria' => array_key_exists('criteria', $parts) ? $parts['criteria'] : $this->expression,
                'orderings' => array_key_exists('orderings', $parts) ? $parts['orderings'] : $this->orderings,
                'joins' => array_key_exists('joins', $parts) ? $parts['joins'] : $this->joins,
                'having' => array_key_exists('joins', $parts) ? $parts['having'] : $this->having,
                'groupBys' => array_key_exists('groupBys', $parts) ? $parts['groupBys'] : $this->groupBys,
                'firstResult' => array_key_exists('firstResult', $parts) ? $parts['firstResult'] : $this->firstResult,
                'maxResults' => array_key_exists('maxResults', $parts) ? $parts['maxResults'] : $this->maxResults,
            ]
        );
    }

    public function getClassFqn(): string
    {
        return $this->classFqn;
    }

    public function hasExpression()
    {
        return null !== $this->expression;
    }

    public function getExpression(): Expression
    {
        return $this->expression;
    }

    public function getOrderings(): array
    {
        return $this->orderings;
    }

    public function getMaxResults()
    {
        return $this->maxResults;
    }

    public function getFirstResult()
    {
        return $this->firstResult;
    }

    public function getJoins(): array
    {
        return $this->joins;
    }

    public function getSelects(): array
    {
        return $this->selects;
    }

    public function getHaving(): Having
    {
        return $this->having;
    }

    public function hasHaving()
    {
        return null !== $this->having;
    }
    
    public function getGroupBys(): array
    {
        return $this->groupBys;
    }
}
