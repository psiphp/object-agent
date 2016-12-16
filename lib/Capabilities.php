<?php

declare(strict_types=1);

namespace Psi\Component\ObjectAgent;

final class Capabilities
{
    private $supportedComparators;
    private $setParent;
    private $queryCount;
    private $queryJoin;
    private $querySelect;
    private $queryHaving;

    private function __construct()
    {
        // this class cannot be instantiated with "new".
    }

    public static function create(array $capabilities): Capabilities
    {
        $defaults = [
            'supported_comparators' => [],
            'can_set_parent' => false,
            'can_query_count' => false,
            'can_query_join' => false,
            'can_query_select' => false,
            'can_query_having' => false,
        ];

        if ($diff = array_diff(array_keys($capabilities), array_keys($defaults))) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown capabilities: "%s". Valid capabilities: "%s"',
                implode('", "', $diff), implode('", "', array_keys($defaults))
            ));
        }

        $capabilities = array_merge($defaults, $capabilities);

        $instance = new self();
        $instance->supportedComparators = (array) $capabilities['supported_comparators'];
        $instance->setParent = (bool) $capabilities['can_set_parent'];
        $instance->queryJoin = (bool) $capabilities['can_query_join'];
        $instance->queryCount = (bool) $capabilities['can_query_count'];
        $instance->querySelect = (bool) $capabilities['can_query_select'];
        $instance->queryHaving = (bool) $capabilities['can_query_having'];

        return $instance;
    }

    public function getSupportedComparators(): array
    {
        return $this->supportedComparators;
    }

    public function canSetParent(): bool
    {
        return $this->setParent;
    }

    public function canQueryCount(): bool
    {
        return $this->queryCount;
    }

    public function canQueryJoin(): bool
    {
        return $this->queryJoin;
    }

    public function canQuerySelect(): bool
    {
        return $this->querySelect;
    }

    public function canQueryHaving(): bool
    {
        return $this->queryHaving;
    }
}
