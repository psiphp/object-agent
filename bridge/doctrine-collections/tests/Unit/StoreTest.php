<?php

declare(strict_types=1);

namespace Psi\Bridge\ObjectAgent\Doctrine\Collections\Tests\Unit;

use Doctrine\Common\Collections\ArrayCollection;
use Psi\Bridge\ObjectAgent\Doctrine\Collections\Store;

class StoreTest extends \PHPUnit_Framework_TestCase
{
    /**
     * It should throw an exception if an object is not the correct class.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage All objects in collection must be of class "Psi\Bridge\ObjectAgent\Doctrine\Collections\Tests\Unit\TestClass
     */
    public function testExceptionIntruder()
    {
        new Store([
            TestClass::class => [
                new TestClass(),
                new \stdClass(),
            ],
        ]);
    }

    /**
     * It should throw an exception if getting a collection for an unknown class.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage No collections available of class "stdClass"
     */
    public function testGetCollectionException()
    {
        $store = new Store([]);
        $store->getCollection(\stdClass::class);
    }

    /**
     * It should create a non-existing collection.
     */
    public function testGetOrCreateCollection()
    {
        $store = new Store([]);
        $created = $store->getOrCreateCollection(\stdClass::class);
        $collection = $store->getOrCreateCollection(\stdClass::class);
        $this->assertInstanceOf(ArrayCollection::class, $collection);
        $this->assertSame($created, $collection);
    }

    /**
     * It should remove an object.
     */
    public function testDelete()
    {
        $store = new Store([
            \stdClass::class => [
                $target = new \stdClass(),
                new \stdClass(),
                new \stdClass(),
            ],
        ]);
        $store->remove($target);
        $collection = $store->getCollection(\stdClass::class);
        $this->assertCount(2, $collection);
    }

    /**
     * It should find an object.
     */
    public function testFind()
    {
        $store = new Store([
            \stdClass::class => [
                $target = new \stdClass(),
                new \stdClass(),
                new \stdClass(),
            ],
        ]);
        $object = $store->find(\stdClass::class, 0);
        $this->assertInstanceOf(\stdClass::class, $object);
        $this->assertSame($target, $object);
    }

    /**
     * It should say if it has a collection or not.
     */
    public function testHasCollection()
    {
        $store = new Store([
            \stdClass::class => [],
        ]);
        $this->assertTrue($store->hasCollection(\stdClass::class));
        $this->assertFalse($store->hasCollection(TestClass::class));
    }
}

class TestClass
{
}
