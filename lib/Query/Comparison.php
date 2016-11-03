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

    // TODO: Contains (like), NULL, NOT NULL, IN

    private static $validTypes = [
        self::EQUALS,
        self::NOT_EQUALS,
        self::GREATER_THAN,
        self::GREATER_THAN_EQUAL,
        self::LESS_THAN,
        self::LESS_THAN_EQUAL,
    ];

    private $comparator;
    private $field;
    private $value;

    public function __construct(string $comparator, $field, $value)
    {
        if (!in_array($comparator, self::$validTypes)) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown comparator "%s". Known comparators: "%s"',
                $comparator,
                implode('", "', self::$validTypes)
            ));
        }

        $this->comparator = $comparator;
        $this->field = $field;
        $this->value = $value;
    }

    public function getComparator()
    {
        return $this->comparator;
    }

    public function getField()
    {
        return $this->field;
    }

    public function getValue()
    {
        return $this->value;
    }
}
