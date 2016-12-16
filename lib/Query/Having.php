<?php

declare(strict_types=1);

namespace Psi\Component\ObjectAgent\Query;

class Having
{
    /**
     * @var Expression
     */
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
