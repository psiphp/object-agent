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

    public function findAgentFor(string $classFqn)
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
}
