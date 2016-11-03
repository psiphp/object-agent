<?php

namespace Psi\Component\ObjectAgent\Event;

use Symfony\Component\EventDispatcher\Event;
use Psi\Component\ObjectAgent\AgentInterface;

final class ObjectEvent extends Event
{
    private $agent;
    private $object;

    public function __construct(AgentInterface $agent, $object)
    {
        $this->object = $object;
        $this->agent = $agent;
    }

    public function getAgent()
    {
        return $this->agent;
    }

    public function getObject()
    {
        return $this->object;
    }
}
