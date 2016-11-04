<?php

declare(strict_types=1);

namespace Psi\Bridge\ObjectAgent\Doctrine\PhpcrOdm;

use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ODM\PHPCR\DocumentManagerInterface;
use Psi\Component\ObjectAgent\AgentInterface;
use Psi\Component\ObjectAgent\Exception\ObjectNotFoundException;
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
            throw new ObjectNotFoundException(sprintf(
                'Could not find document with identifier "%s" (class "%s")',
                $identifier, null === $class ? '<null>' : $class
            ));
        }

        return $object;
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
        $visitor = new ExpressionVisitor(
            $queryBuilder,
            $sourceAlias
        );

        $visitor->dispatch($query->getExpression());

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * Return the document mangaer instance (for use in events).
     */
    public function getDocumentManager(): DocumentManagerInterface
    {
        return $this->documentManager;
    }
}
