<?php

declare(strict_types=1);

namespace Psi\Component\ObjectAgent\Query;

class Conjunction implements Expression
{
    private $expressions;

    public function __construct(array $expressions)
    {
        // ensure types
        array_map(function (Expression $expr) {
        }, $expressions);

        $this->expressions = $expressions;
    }

    public function getExpressions()
    {
        return $this->expressions;
    }
}
