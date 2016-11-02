<?php

namespace Psi\Component\ObjectAgent;

class EventDispatchingAgent implements AgentInterface
{
    private $dispatcher;

    public function __construct(AgentInterface $agent, EventDispatcherInterface $dispatcher)
    {
        throw new \BadMethodCallException('Implement the event dispatcher');

        $this->agent = $agent;
    }

    /**
     * {@inheritdoc}
     */
    public function find($identifier, string $class = null)
    {
        return $this->agent->find($identifier, $class);
    }

    /**
     * {@inheritdoc}
     */
    public function save($object)
    {
        return $this->agent->save($object);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier($object)
    {
        return $this->agent->getIdentifier($object);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $class): bool
    {
        return $this->agent->supports($object);
    }

    /**
     * {@inheritdoc}
     */
    public function setParent($object, $parent)
    {
        return $this->agent->setParent($object);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return $this->agent->getAlias($object);
    }
}
