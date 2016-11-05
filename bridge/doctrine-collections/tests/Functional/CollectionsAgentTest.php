<?php

declare(strict_types=1);

namespace Psi\Bridge\ObjectAgent\Doctrine\Orm\Tests\Functional;

use Psi\Bridge\ObjectAgent\Doctrine\Collections\CollectionsAgent;
use Psi\Bridge\ObjectAgent\Doctrine\Collections\Store;
use Psi\Component\ObjectAgent\Query\Query;
use Psi\Component\ObjectAgent\Tests\Functional\Model\Page;

class CollectionsAgentTest extends \PHPUnit_Framework_TestCase
{
    private $agent;
    private $store;

    public function setUp()
    {
        $this->store = new Store([
            Page::class => [
                new Page('one'),
                new Page('two'),
                new Page('three'),
                new Page('four'),
            ],
        ]);
        $this->agent = new CollectionsAgent($this->store);
    }

    /**
     * It should find a object.
     */
    public function testFind()
    {
        $page = $this->createPage();

        $object = $this->agent->find(1, Page::class);
        $this->assertSame($page, $object);
    }

    /**
     * It should throw an exception if no class argument is given to find.
     *
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage The class argument is mandatory
     */
    public function testNoClass()
    {
        $this->agent->find(12);
    }

    /**
     * It should throw an exception if the object was not found.
     *
     * @expectedException Psi\Component\ObjectAgent\Exception\ObjectNotFoundException
     * @expectedExceptionMessage Could not find object of class "Psi\Component\ObjectAgent\Tests\Functional\Model\Page" with identifier "7"
     */
    public function testFindNotFound()
    {
        $this->agent->find(7, Page::class);
    }

    /**
     * It should thro.
     */
    public function testSave()
    {
        $page = new Page();
        $page->id = 5;
        $this->agent->save($page);
    }

    /**
     * It should delete.
     */
    public function testDelete()
    {
        $page = $this->createPage();
        $this->agent->delete($page);

        $object = $this->store->find(Page::class, $page->id);
        $this->assertNull($object);
    }

    /**
     * It should return a object's identifier (a UUID).
     */
    public function testGetIdentifier()
    {
        $page = $this->createPage();
        $identifier = $this->agent->getIdentifier($page);
        $this->assertNotNull($identifier);
    }

    /**
     * It should throw a BadMethodCallException if set parent is called.
     *
     * @expectedException Psi\Component\ObjectAgent\Exception\BadMethodCallException
     */
    public function testSetParent()
    {
        $parent = $this->createPage();
        $page = new Page();
        $this->agent->setParent($page, $parent);
    }

    /**
     * It should say if it supports a given object.
     */
    public function testSupports()
    {
        $this->assertTrue($this->agent->supports(Page::class));
        $this->assertFalse($this->agent->supports(\stdClass::class));
    }

    public function testQuery()
    {
        $this->createPage('Foobar');
        $this->createPage('Hello');
        $query = Query::create(Page::class, Query::composite(
            'and',
            Query::comparison('eq', 'title', 'Hello')
        ));
        $results = $this->agent->query($query);
        $this->assertCount(1, $results);
    }

    private function createPage($title = 'Hello World')
    {
        static $id = 1;
        $page = new Page();
        $page->id = $id++;
        $page->title = $title;
        $this->agent->save($page);
        $collection = $this->store->getOrCreateCollection(Page::class);
        $collection->set($page->id, $page);

        return $page;
    }
}
