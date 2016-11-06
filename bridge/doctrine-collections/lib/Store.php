<?php

declare(strict_types=1);

namespace Psi\Bridge\ObjectAgent\Doctrine\Collections;

use Doctrine\Common\Collections\ArrayCollection;

final class Store
{
    private $collections;

    public function __construct(array $collections)
    {
        foreach ($collections as $classFqn => $collection) {
            $this->addCollection($classFqn, $collection);
        }
    }

    public function addCollection($classFqn, array $collection)
    {
        array_map(function ($object) use ($classFqn) {
            if (get_class($object) !== $classFqn) {
                throw new \InvalidArgumentException(sprintf(
                    'All objects in collection must be of class "%s", got "%s"',
                    $classFqn, get_class($object)
                ));
            }
        }, $collection);

        $this->collections[$classFqn] = new ArrayCollection($collection);
    }

    public function getCollection(string $classFqn): ArrayCollection
    {
        if (false === isset($this->collections[$classFqn])) {
            throw new \InvalidArgumentException(sprintf(
                'No collections available of class "%s"',
                $classFqn
            ));
        }

        return $this->collections[$classFqn];
    }

    public function hasCollection(string $classFqn)
    {
        return isset($this->collections[$classFqn]);
    }

    public function find($classFqn, $identifier)
    {
        $collection = $this->getCollection($classFqn);

        if (false === isset($collection[$identifier])) {
            return;
        }

        return $collection[$identifier];
    }

    public function delete($object)
    {
        $classFqn = get_class($object);
        $collection = $this->getCollection($classFqn);
        $collection->removeElement($object);
    }

    public function getOrCreateCollection($classFqn): ArrayCollection
    {
        if (!$this->hasCollection($classFqn)) {
            $this->collections[$classFqn] = new ArrayCollection();
        }

        return $this->getCollection($classFqn);
    }
}
