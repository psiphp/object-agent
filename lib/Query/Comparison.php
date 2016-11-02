<?php

declare(strict_types=1);

namespace Psi\Component\ObjectAgent\Query;

class Comparison implements Expression
{
    const EQUALS = 'eq';
    const NOT_EQUALS = 'neq';
    const GREATER_THAN = 'gt';
    const GREATER_THAN_EQUAL = 'gte';
    const LESS_THAN = 'lt';
    const LESS_THAN_EQUAL = 'lte';

    private static $validTypes = [
        self::EQUALS,
        self::NOT_EQUALS,
        self::GREATER_THAN,
        self::GREATER_THAN_EQUAL,
        self::LESS_THAN,
        self::LESS_THAN_EQUAL,
    ];

    private $comparator;
    private $value1;
    private $value2;

    public function __construct(string $comparator, $value1, $value2)
    {
        if (!in_array($comparator, self::$validTypes)) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown comparator "%s". Known comparators: "%s"',
                $comparator,
                implode('", "', self::$validTypes)
            ));
        }

        $this->comparator = $comparator;
        $this->value1 = $value1;
        $this->value2 = $value2;
    }

    public function getComparator()
    {
        return $this->comparator;
    }

    public function getValue1()
    {
        return $this->value1;
    }

    public function getValue2()
    {
        return $this->value2;
    }
}
