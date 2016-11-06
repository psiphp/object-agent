<?php

namespace Psi\Component\ObjectAgent\Query;

/**
 * Query::from(Foo::class)
 *   ->where(
 *       Query::and(
 *           Query::comparison('eq', 'foo', 'bar'),
 *           Query::comparison('gt', 10, 5),
 *       )
 *   );.
 */
final class Query
{
    private $classFqn;
    private $expression;
    private $orderings;
    private $firstResult;
    private $maxResults;

    public function __construct(
        string $classFqn,
        Expression $expression = null,
        array $orderings = null,
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
        Expression $expression = null,
        array $orderings = [],
        int $firstResult = null,
        int $maxResults = null
    ) {
        return new self($classFqn, $expression, $orderings, $firstResult, $maxResults);
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
