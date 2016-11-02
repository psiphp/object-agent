<?php

namespace Psi\Component\ObjectAgent\Query;

/**
 * Query::from(Foo::class)
 *   ->where(
 *       Query::and(
 *           Query::comparison('eq', 'foo', 'bar'),
 *           Query::comparison('gt', 10, 5),
 *       )
 *   );
 */
final class Query
{
    private $classFqn;
    private $expression;

    private function __construct()
    {
    }

    public static function create(string $classFqn, Expression $expression)
    {
        $instance = new self();
        $instance->classFqn = $classFqn;
        $instance->expression = $expression;

        return $instance;
    }

    public function getClassFqn()
    {
        return $this->classFqn;
    }

    public function getExpression()
    {
        return $this->expression;
    }

    public static function comparison(string $comparator, $value1, $value2): Comparison
    {
        return new Comparison($comparator, $value1, $value2);
    }

    public static function and(...$expressions): Conjunction
    {
        return new Conjunction($expressions);
    }

    public static function or(...$expressions): Disjunction
    {
        return new Disjunction($expressions);
    }

    public static function not(Expression $expression)
    {
        return new Negation($expression);
    }
}
