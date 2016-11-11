<?php

namespace Psi\Component\ObjectAgent;

final class Capabilities
{
    private $supportedComparators;
    private $setParent;
    private $queryCount;

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
        $instance->queryCount = (bool) $capabilities['can_query_count'];

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
}
