<?php

declare(strict_types=1);

namespace Psi\Component\ObjectAgent\Query;

final class Query
{
    private $classFqn;
    private $expression;
    private $orderings;
    private $firstResult;
    private $maxResults;

    private function __construct(
        string $classFqn,
        array $selects = [],
        array $joins = [],
        Expression $expression = null,
        array $orderings = [],
        int $firstResult = null,
        int $maxResults = null
    ) {
        $this->classFqn = $classFqn;
        $this->expression = $expression;
        $this->orderings = $orderings;
        $this->firstResult = $firstResult;
        $this->maxResults = $maxResults;
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

        return new self($classFqn, $query['selects'], $query['joins'], $query['criteria'], $query['orderings'], $query['firstResult'], $query['maxResults']);
    }

    public static function comparison(string $comparator, $value1, $value2): Comparison
    {
        return new Comparison($comparator, $value1, $value2);
    }

    public static function composite(string $type, ...$expressions): Composite
    {
        return new Composite($type, $expressions);
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
}
