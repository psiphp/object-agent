<?php

declare(strict_types=1);

namespace Psi\Bridge\ObjectAgent\Doctrine\Collections;

use Doctrine\Common\Collections\Criteria;
use Psi\Component\ObjectAgent\AgentInterface;
use Psi\Component\ObjectAgent\Exception\BadMethodCallException;
use Psi\Component\ObjectAgent\Exception\ObjectNotFoundException;
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
    public function find($identifier, string $classFqn = null)
    {
        if (null === $classFqn) {
            throw BadMethodCallException::classArgumentIsMandatory(__CLASS__);
        }

        $object = $this->store->find($classFqn, $identifier);

        if (null === $object) {
            throw new ObjectNotFoundException(sprintf(
                'Could not find object of class "%s" with identifier "%s"',
                $classFqn, $identifier
            ));
        }

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function save($object)
    {
        // do nothing ...
    }

    /**
     * {@inheritdoc}
     */
    public function delete($object)
    {
        $this->store->delete($object);
    }

    /**
     * {@inheritdoc}
     */
    public function query(Query $query): \Traversable
    {
        $collection = $this->store->getCollection($query->getClassFqn());
        $expressionBuilder = Criteria::expr();
        $visitor = new CollectionsVisitor($expressionBuilder);
        $doctrineExpression = $visitor->dispatch($query->getExpression());
        $criteria = new Criteria($doctrineExpression);

        return $collection->matching($criteria);
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
}
