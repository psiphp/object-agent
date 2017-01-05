<?php

declare(strict_types=1);

namespace Psi\Bridge\ObjectAgent\Doctrine\Collections;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Psi\Component\ObjectAgent\AgentInterface;
use Psi\Component\ObjectAgent\Capabilities;
use Psi\Component\ObjectAgent\Exception\BadMethodCallException;
use Psi\Component\ObjectAgent\Exception\ObjectNotFoundException;
use Psi\Component\ObjectAgent\Query\Comparison;
use Psi\Component\ObjectAgent\Query\Query;

class CollectionsAgent implements AgentInterface
{
    private $store;

    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $classFqn): bool
    {
        return $this->store->hasCollection($classFqn);
    }

    /**
     * {@inheritdoc}
     */
    public function getCapabilities(): Capabilities
    {
        return Capabilities::create([
            'can_query_join' => false,
            'can_set_parent' => false,
            'can_query_count' => true,
            'supported_comparators' => [
                Comparison::EQUALS,
                Comparison::NOT_EQUALS,
                Comparison::LESS_THAN,
                Comparison::LESS_THAN_EQUAL,
                Comparison::GREATER_THAN,
                Comparison::GREATER_THAN_EQUAL,
                Comparison::IN,
                Comparison::NOT_IN,
                Comparison::CONTAINS,
                Comparison::NULL,
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function find($identifier, string $classFqn = null)
    {
        if (null === $classFqn) {
            throw BadMethodCallException::classArgumentIsMandatory(__CLASS__);
        }

        $object = $this->store->find($classFqn, $identifier);

        if (null === $object) {
            throw ObjectNotFoundException::forClassAndIdentifier($classFqn, $identifier);
        }

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function findMany(array $identifiers, string $classFqn = null)
    {
        $collection = [];
        foreach ($identifiers as $identifier) {
            $collection[] = $this->find($identifier, $classFqn);
        }

        return new ArrayCollection($collection);
    }

    /**
     * {@inheritdoc}
     */
    public function persist($object)
    {
        $this->store->persist($object);
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        // do nothing ...
    }

    /**
     * {@inheritdoc}
     */
    public function remove($object)
    {
        $this->store->remove($object);
    }

    /**
     * {@inheritdoc}
     */
    public function query(Query $query): \Traversable
    {
        return $this->doQuery(
            $query,
            $query->getFirstResult(),
            $query->getMaxResults()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function queryCount(Query $query): int
    {
        return count($this->doQuery($query, 0));
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier($object)
    {
        foreach ($this->store->getCollection(get_class($object)) as $identifier => $element) {
            if ($element === $object) {
                return $identifier;
            }
        }

        throw new \RuntimeException(sprintf(
            'Could not find identifier for object of class "%s"',
            get_class($object)
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function setParent($object, $parent)
    {
        throw BadMethodCallException::setParentNotSupported(__CLASS__);
    }

    private function doQuery(Query $query, int $firstResult = null, int $maxResults = null)
    {
        $collection = $this->store->getCollection($query->getClassFqn());

        $doctrineExpression = null;
        if ($query->hasExpression()) {
            $expressionBuilder = Criteria::expr();
            $visitor = new CollectionsVisitor($expressionBuilder);
            $doctrineExpression = $visitor->dispatch($query->getExpression());
        }

        $criteria = new Criteria(
            $doctrineExpression,
            $query->getOrderings(),
            $firstResult,
            $maxResults
        );

        return $collection->matching($criteria);
    }
}
