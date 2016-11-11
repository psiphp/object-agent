<?php

declare(strict_types=1);

namespace Psi\Bridge\ObjectAgent\Doctrine\PhpcrOdm;

use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ODM\PHPCR\DocumentManagerInterface;
use Psi\Component\ObjectAgent\AgentInterface;
use Psi\Component\ObjectAgent\Capabilities;
use Psi\Component\ObjectAgent\Exception\BadMethodCallException;
use Psi\Component\ObjectAgent\Exception\ObjectNotFoundException;
use Psi\Component\ObjectAgent\Query\Comparison;
use Psi\Component\ObjectAgent\Query\Query;

class PhpcrOdmAgent implements AgentInterface
{
    private $documentManager;

    public function __construct(
        DocumentManagerInterface $documentManager
    ) {
        $this->documentManager = $documentManager;
    }

    /**
     * {@inheritdoc}
     */
    public function find($identifier, string $class = null)
    {
        $object = $this->documentManager->find($class, $identifier);

        if (null === $object) {
            throw ObjectNotFoundException::forClassAndIdentifier($class, $identifier);
        }

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function getCapabilities(): Capabilities
    {
        return Capabilities::create([
            'can_set_parent' => true,
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
    public function save($object)
    {
        $this->documentManager->persist($object);
        $this->documentManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function delete($object)
    {
        $this->documentManager->remove($object);
        $this->documentManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier($object)
    {
        $objectFqn = ClassUtils::getRealClass(get_class($object));
        $metadata = $this->documentManager->getClassMetadata($objectFqn);
        $uuidFieldName = $metadata->getUuidFieldName();

        if (!$uuidFieldName) {
            throw new \RuntimeException(sprintf(
                'Document "%s" does not have a UUID-mapped property. All '.
                'PHPCR-ODM documents must have a mapped UUID proprety.',
                $objectFqn
            ));
        }

        $node = $this->documentManager->getNodeForDocument($object);

        return $node->getIdentifier();
    }

    /**
     * {@inheritdoc}
     */
    public function setParent($object, $parent)
    {
        $objectFqn = ClassUtils::getRealClass(get_class($object));
        $metadata = $this->documentManager->getClassMetadata($objectFqn);
        $parentField = $metadata->parentMapping;

        if (!$parentField) {
            throw new \RuntimeException(sprintf(
                'Document "%s" does not have a ParentDocument mapping All '.
                'PHPCR-ODM documents must have a mapped parent proprety.',
                $objectFqn
            ));
        }

        $metadata->setFieldValue($object, $parentField, $parent);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $class): bool
    {
        $metadataFactory = $this->documentManager->getMetadataFactory();

        $supports = false;
        try {
            $metadataFactory->getMetadataFor(ClassUtils::getRealClass($class));
            $supports = true;
        } catch (MappingException $exception) {
            // no metadata - class is not known to phpcr-odm
        }

        return $supports;
    }

    /**
     * {@inheritdoc}
     */
    public function query(Query $query): \Traversable
    {
        $sourceAlias = 'a';
        $queryBuilder = $this->documentManager->getRepository($query->getClassFqn())->createQueryBuilder($sourceAlias);

        if ($query->hasExpression()) {
            $visitor = new ExpressionVisitor(
                $queryBuilder,
                $sourceAlias
            );

            $visitor->dispatch($query->getExpression());
        }

        $orderBy = $queryBuilder->orderBy();
        foreach ($query->getOrderings() as $field => $order) {
            $order = strtolower($order);
            $orderBy->{$order}()->field($sourceAlias . '.' .  $field);
        }

        if (null !== $query->getFirstResult()) {
            $queryBuilder->setFirstResult($query->getFirstResult());
        }

        if (null !== $query->getMaxResults()) {
            $queryBuilder->setMaxResults($query->getMaxResults());
        }

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function queryCount(Query $query): int
    {
        throw BadMethodCallException::queryCountNotSupported(__CLASS__);
    }

    /**
     * Return the document mangaer instance (for use in events).
     */
    public function getDocumentManager(): DocumentManagerInterface
    {
        return $this->documentManager;
    }
}
