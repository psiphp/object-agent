<?php

declare(strict_types=1);

namespace Psi\Bridge\ObjectAgent\Doctrine\Orm;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Psi\Component\ObjectAgent\AgentInterface;
use Psi\Component\ObjectAgent\Capabilities;
use Psi\Component\ObjectAgent\Exception\ObjectNotFoundException;
use Psi\Component\ObjectAgent\Query\Comparison;
use Psi\Component\ObjectAgent\Query\Query;

class OrmAgent implements AgentInterface
{
    const SOURCE_ALIAS = 'a';

    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getCapabilities(): Capabilities
    {
        return Capabilities::create([
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
                Comparison::NOT_CONTAINS,
                Comparison::NULL,
                Comparison::NOT_NULL,
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function find($identifier, string $class = null)
    {
        if (null === $class) {
            throw new \BadMethodCallException(sprintf(
                'The "class" argument is mandatory for the doctrine ORM (when called with identifier "%s")',
                $identifier
            ));
        }

        $object = $this->entityManager->find($class, $identifier);

        if (null === $object) {
            throw ObjectNotFoundException::forClassAndIdentifier($class, $identifier);
        }

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function save($object)
    {
        $this->entityManager->persist($object);
        $this->entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function delete($object)
    {
        $this->entityManager->remove($object);
        $this->entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier($object)
    {
        $objectFqn = ClassUtils::getRealClass(get_class($object));
        $metadata = $this->entityManager->getClassMetadata($objectFqn);
        $ids = $metadata->getIdentifierValues($object);

        if (count($ids) !== 1) {
            throw new \RuntimeException(sprintf(
                'Object agent does not support ORM objects with composite IDs (for class "%s")',
                $objectFqn
            ));
        }

        return reset($ids);
    }

    /**
     * {@inheritdoc}
     */
    public function setParent($object, $parent)
    {
        throw new \BadMethodCallException(
            'Doctrine ORM is not a hierarhical storage system, cannot set parent.'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $class): bool
    {
        $metadataFactory = $this->entityManager->getMetadataFactory();

        $supports = false;
        try {
            $metadataFactory->getMetadataFor(ClassUtils::getRealClass($class));
            $supports = true;
        } catch (MappingException $exception) {
            // no metadata - class is not known to the ORM
        }

        return $supports;
    }

    /**
     * {@inheritdoc}
     */
    public function query(Query $query): \Traversable
    {
        $queryBuilder = $this->getQueryBuilder($query);

        foreach ($query->getOrderings() as $field => $order) {
            $queryBuilder->addOrderBy(self::SOURCE_ALIAS . '.' . $field, $order);
        }

        if (null !== $query->getFirstResult()) {
            $queryBuilder->setFirstResult($query->getFirstResult());
        }

        if (null !== $query->getMaxResults()) {
            $queryBuilder->setMaxResults($query->getMaxResults());
        }

        return new ArrayCollection(
            $queryBuilder->getQuery()->execute()
        );
    }

    public function queryCount(Query $query): int
    {
        $queryBuilder = $this->getQueryBuilder($query);
        $paginator = new Paginator($queryBuilder->getQuery());

        return count($paginator);
    }

    /**
     * Return the entity manager instance (for use in events).
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    private function getQueryBuilder(Query $query): QueryBuilder
    {
        $queryBuilder = $this->entityManager->getRepository($query->getClassFqn())->createQueryBuilder(self::SOURCE_ALIAS);
        $visitor = new ExpressionVisitor(
            $queryBuilder->expr(),
            self::SOURCE_ALIAS
        );

        if ($query->hasExpression()) {
            $expr = $visitor->dispatch($query->getExpression());
            $queryBuilder->where($expr);
            $queryBuilder->setParameters($visitor->getParameters());
        }

        return $queryBuilder;
    }
}
