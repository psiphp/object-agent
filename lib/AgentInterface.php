<?php

declare(strict_types=1);

namespace Psi\Component\ObjectAgent;

use Psi\Component\ObjectAgent\Query\Query;

interface AgentInterface
{
    /**
     * Return true if this agent can handle the given object class identifier.
     */
    public function supports(string $class): bool;

    public function getCapabilities(): Capabilities;

    /**
     * Find an object by its identifier and optionally a class identifier.
     *
     * If the object is not found an ObjectNotFoundException MUST be thrown.
     *
     * If the underlying storage layer requires the class argument then a
     * BadMethodCallException MUST be thrown in the case that the user does not
     * provide it.
     *
     * @param int|string $identifier
     *
     * @throws Exception\ObjectNotFoundException
     * @throws Psi\Component\ObjectAgent\Exception\BadMethodCallException
     *
     * @return object
     */
    public function find($identifier, string $class = null);

    /**
     * Save the given object and flush the storage.
     *
     * @param object $object
     */
    public function save($object);

    /**
     * Remove the given object and flush the storage.
     *
     * @param object $object
     */
    public function delete($object);

    /**
     * Perform a query and return a collection of objects.
     */
    public function query(Query $query): \Traversable;

    /**
     * Return the number of records that would be returned by the query.
     *
     * If the agent does not support query counts then a
     * BadMethodCallException MUST be thrown.
     */
    public function queryCount(Query $query): int;

    /**
     * Return the identifier for the given object.
     *
     * @return int|string
     */
    public function getIdentifier($object);

    /**
     * Set the parent object on a given object.
     *
     * If the agent does not represent a hierarchical storage layer, then a
     * BadMethodCallException MUST be thrown.
     *
     * @param object $object
     * @param object $parent
     *
     * @throws Psi\Component\ObjectAgent\Exception\BadMethodCallException
     */
    public function setParent($object, $parent);
}
