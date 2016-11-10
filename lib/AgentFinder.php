<?php

declare(strict_types=1);

namespace Psi\Component\ObjectAgent;

class AgentFinder
{
    private $agents;

    public function __construct(array $agents)
    {
        array_map(function (AgentInterface $agent) {
        }, $agents);
        $this->agents = $agents;
    }

    public function findFor(string $classFqn)
    {
        foreach ($this->agents as $agent) {
            if ($agent->supports($classFqn)) {
                return $agent;
            }
        }

        $classes = array_map(function ($element) {
            return get_class($element);
        }, $this->agents);

        throw new Exception\AgentNotFoundException(sprintf(
            'Could not find an agent supporting class "%s". Registered agents: "%s"',
            $classFqn, implode('", "', $classes)
        ));
    }

    public function get(string $name)
    {
        if (!isset($this->agents[$name])) {
            throw new Exception\AgentNotFoundException(sprintf(
                'Could not find an agent named "%s". Registered agent names: "%s"',
                $name, implode('", "', array_keys($this->agents))
            ));
        }

        return $this->agents[$name];
    }

    public function getName(AgentInterface $unknownAgent)
    {
        foreach ($this->agents as $name => $agent) {
            if ($agent === $unknownAgent) {
                return $name;
            }
        }

        throw new \RuntimeException(sprintf(
            'Could not identify agent of class "%s"',
            get_class($unknownAgent)
        ));
    }
}
