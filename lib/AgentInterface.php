<?php

declare(strict_types=1);

namespace Psi\Component\ObjectAgent;

use Psi\Component\ObjectAgent\Query\Query;

interface AgentInterface
{
    /**
     * Find an object by its identifier.
     *
     * @param int|string $identifier
     * @throws ObjectNotFound
     *
     * @return object
     */
    public function find($identifier);

    /**
     * Save the given object and flush the storage.
     *
     * @param object $object
     */
    public function save($object);

    /**
     * Perform a query and return a collection of objects.
     *
     * @param Query $query
     */
    public function query(Query $query): \Traversable;

    /**
     * Return the identifier for the given object.
     *
     * @return int|string
     */
    public function getIdentifier($object);

    /**
     * Return true if this agent can handle the given object class.
     *
     * @param string $class
     *
     * @return bool
     */
    public function supports(string $class): bool;

    /**
     * Return the url-safe alias to use for this agent.
     *
     * @return string
     */
    public function getAlias();

    /**
     * Set the parent object on a given object.
     *
     * @param object $object
     * @param object $parent
     */
    public function setParent($object, $parent);
}
