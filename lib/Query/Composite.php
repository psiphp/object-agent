<?php

declare(strict_types=1);

namespace Psi\Component\ObjectAgent\Query;

class Composite implements Expression
{
    const AND = 'and';
    const OR = 'or';

    private static $validTypes = [
        self::AND,
        self::OR,
    ];

    private $expressions;
    private $type;

    public function __construct(string $type, array $expressions)
    {
        // ensure types
        array_map(function (Expression $expr) {
        }, $expressions);

        if (!in_array($type, self::$validTypes)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid composite type "%s", must be one of "%s"',
                $type, implode('", "', self::$validTypes)
            ));
        }

        $this->expressions = $expressions;
        $this->type = $type;
    }

    public function getExpressions()
    {
        return $this->expressions;
    }

    public function getType()
    {
        return $this->type;
    }
}
