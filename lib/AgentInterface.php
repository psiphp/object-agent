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

    /**
     * Return the capabilities object which indicates which features are
     * supported by the underlying storage engine.
     */
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
     * As with #find but for many identifiers.
     *
     * @param int|string[] $identifiers
     *
     * @throws Exception\ObjectNotFoundException
     * @throws Psi\Component\ObjectAgent\Exception\BadMethodCallException
     *
     * @return object[]
     */
    public function findMany(array $identifiers, string $class = null);

    /**
     * Persist the given object.
     *
     * @param object $object
     */
    public function persist($object);

    /**
     * Remove the given object.
     *
     * @param object $object
     */
    public function remove($object);

    /**
     * Flush the storage.
     */
    public function flush();

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
     * Return the canonical class FQN.
     *
     * In some cases the class FQN may be a proxy (e.g. __PROXY__/Foobar/Entity).
     */
    public function getCanonicalClassFqn(string $classFqn): string;

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
