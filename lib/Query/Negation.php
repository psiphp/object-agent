<?php

namespace Psi\Component\ObjectAgent\Query;

class Negation implements Expression
{
    private $expression;

    public function __construct(Expression $expression)
    {
        $this->expression = $expression;
    }

    public function getExpression()
    {
        return $this->expression;
    }
}
