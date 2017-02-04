<?php

declare(strict_types=1);

namespace Psi\Component\ObjectAgent\Query\Converter;

use Psi\Component\ObjectAgent\Query\Comparison;
use Psi\Component\ObjectAgent\Query\Composite;
use Psi\Component\ObjectAgent\Query\Join;
use Psi\Component\ObjectAgent\Query\Query;

class ArrayConverter
{
    private static $comparisons = [
        Comparison::EQUALS,
        Comparison::NOT_EQUALS,
        Comparison::GREATER_THAN,
        Comparison::GREATER_THAN_EQUAL,
        Comparison::LESS_THAN,
        Comparison::LESS_THAN_EQUAL,
        Comparison::NULL,
        Comparison::NOT_NULL,
        Comparison::IN,
        Comparison::NOT_IN,
        Comparison::CONTAINS,
        Comparison::NOT_CONTAINS,
    ];

    private static $composites = [
        Composite::AND,
        Composite::OR,
    ];

    public function __invoke(array $query)
    {
        $query = $this->merge([
            'selects' => [],
            'from' => null,
            'criteria' => null,
            'orderings' => [],
            'joins' => [],
            'firstResult' => null,
            'maxResults' => null,
        ], $query);

        if (null === $query['from']) {
            throw new \InvalidArgumentException(sprintf(
                'You must specify the "from" part of the query.'
            ));
        }

        if ($query['criteria']) {
            $query['criteria'] = new Composite(Composite::AND, $this->walkCriteria($query['criteria']));
        }

        $query['joins'] = $this->walkJoins($query['joins']);
        $from = $query['from'];
        unset($query['from']);

        return Query::create($from, $query);
    }

    private function walkJoins(array $joins)
    {
        $joinObjects = [];

        foreach ($joins as $join) {
            $join = $this->merge([
                'type' => Join::INNER_JOIN,
                'join'=> null,
                'alias' => null,
                'from' => null,
                'condition' => null,
            ], $join, ['join', 'alias']);

            $joinObjects[] = new Join($join['join'], $join['alias'], $join['type'], $join['from'], $join['condition']);
        }

        return $joinObjects;
    }

    private function walkCriteria(array $exprs, array $original = null)
    {
        $original = $original ?: $exprs;

        $criterias = [];
        foreach ($exprs as $operator => $right) {
            if (in_array($operator, self::$comparisons)) {
                if (!is_array($right)) {
                    throw new \InvalidArgumentException(sprintf(
                        'Comparison must have an array as the right-sided value, got "%s"',
                        gettype($right)
                    ));
                }

                foreach ($right as $field => $value) {
                    $criterias[] = new Comparison($operator, $field, $value);
                }

                continue;
            }

            if (in_array($operator, self::$composites)) {
                $criterias[] = new Composite($operator, $this->walkCriteria($right, $original));
                continue;
            }

            throw new \InvalidArgumentException(sprintf(
                'Unknown expression operator "%s" in "%s"', $operator, $this->toString($original)
            ));
        }

        return $criterias;
    }

    private function toString(array $expr)
    {
        return json_encode($expr);
    }

    private function merge(array $defaults, array $values, array $required = [])
    {
        if ($diff = array_diff($required, array_keys($values))) {
            throw new \InvalidArgumentException(sprintf(
                'Keys "%s" are required for "%s"',
                implode('", "', $diff), json_encode($values)
            ));
        }

        if ($diff = array_diff(array_keys($values), array_keys($defaults))) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid query keys "%s", valid keys: "%s"',
                implode('", "', $diff), implode('", "', array_keys($defaults))
            ));
        }

        return array_merge($defaults, $values);
    }
}
