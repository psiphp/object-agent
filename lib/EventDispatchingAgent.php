<?php

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
    public function find($identifier, string $class = null)
    {
        return $this->agent->find($identifier, $class);
    }

    /**
     * {@inheritdoc}
     */
    public function save($object)
    {
        $this->dispatch(Events::PRE_SAVE, $object);
        $this->agent->save($object);
        $this->dispatch(Events::POST_SAVE, $object);
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
    public function delete($object)
    {
        $this->dispatch(Events::PRE_DELETE, $object);
        $this->agent->delete($object);
        $this->dispatch(Events::POST_DELETE, $object);
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
