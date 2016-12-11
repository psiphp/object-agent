<?php

declare(strict_types=1);

namespace Psi\Component\ObjectAgent;

use Psi\Component\ObjectAgent\Event\ObjectEvent;
use Psi\Component\ObjectAgent\Query\Query;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EventDispatchingAgent implements AgentInterface
{
    private $dispatcher;
    private $agent;

    public function __construct(
        AgentInterface $agent,
        EventDispatcherInterface $dispatcher
    ) {
        $this->agent = $agent;
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function getCapabilities(): Capabilities
    {
        return $this->agent->getCapabilities();
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
    public function findMany(array $identifiers, string $class = null)
    {
        return $this->agent->findMany($identifiers, $class);
    }

    /**
     * {@inheritdoc}
     */
    public function persist($object)
    {
        $this->dispatch(Events::PRE_PERSIST, $object);
        $this->agent->persist($object);
        $this->dispatch(Events::POST_PERSIST, $object);
    }

    /**
     * {@inheritdoc}
     */
    public function query(Query $query): \Traversable
    {
        return $this->agent->query($query);
    }

    /**
     * {@inheritdoc}
     */
    public function queryCount(Query $query): int
    {
        return $this->agent->queryCount($query);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($object)
    {
        $this->dispatch(Events::PRE_REMOVE, $object);
        $this->agent->remove($object);
        $this->dispatch(Events::POST_REMOVE, $object);
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        $this->agent->flush();
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
        return $this->agent->supports($class);
    }

    /**
     * {@inheritdoc}
     */
    public function setParent($object, $parent)
    {
        return $this->agent->setParent($object, $parent);
    }

    private function dispatch(string $eventName, $object)
    {
        $this->dispatcher->dispatch($eventName, new ObjectEvent($this->agent, $object));
    }
}
