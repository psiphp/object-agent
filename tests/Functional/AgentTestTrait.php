<?php

namespace Psi\Component\ObjectAgent\Tests\Functional;

use Psi\Component\ObjectAgent\Capabilities;
use Psi\Component\ObjectAgent\Exception\ObjectNotFoundException;
use Psi\Component\ObjectAgent\Query\Query;
use Psi\Component\ObjectAgent\Tests\Functional\Model\Page;

trait AgentTestTrait
{
    /**
     * It should return its capabilities.
     */
    public function testCapabilities()
    {
        $capabilities = $this->agent->getCapabilities();
        $this->assertInstanceOf(Capabilities::class, $capabilities);
    }

    /**
     * It should find a object.
     */
    public function testFind()
    {
        $page = $this->createPage();

        $object = $this->agent->find($page->id, Page::class);
        $this->assertSame($page, $object);
    }

    /**
     * It should throw an exception if the object was not found.
     *
     * @expectedException Psi\Component\ObjectAgent\Exception\ObjectNotFoundException
     * @expectedExceptionMessage Could not find object
     */
    public function testFindNotFound()
    {
        $this->createPage('Foobar');
        $this->createPage('Hello');
        $fo = $this->agent->find(7, Page::class);
    }

    /**
     * It should thro.
     */
    public function testSave()
    {
        $page = $this->createPage();
        $this->agent->save($page);
    }

    /**
     * It should delete.
     */
    public function testDelete()
    {
        $page = $this->createPage();
        $this->agent->delete($page);

        try {
            $object = $this->agent->find($page->id, Page::class);
            $this->fail('Objecvt was not deleted');
        } catch (ObjectNotFoundException $e) {
        }
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
     * It should say if it supports a given object.
     */
    public function testSupports()
    {
        $this->assertTrue($this->agent->supports(Page::class));
        $this->assertFalse($this->agent->supports(\stdClass::class));
    }

    /**
     * It should perform a query.
     */
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

    /**
     * It should return all objects if no expression is provided.
     */
    public function testQueryNoExpression()
    {
        $this->createPage();
        $this->createPage();
        $this->createPage();
        $this->createPage();
        $query = Query::create(Page::class);
        $results = $this->agent->query($query);
        $this->assertCount(4, $results);
    }

    /**
     * It should limit the results.
     */
    public function testQueryLimit()
    {
        $this->createPage('aaaa');
        $this->createPage('aaaa');
        $this->createPage('aaaa');
        $this->createPage('zzzz');
        $query = Query::create(Page::class, null, [], 0, 2);
        $results = $this->agent->query($query);
        $this->assertCount(2, $results);
    }

    /**
     * It should set the first result offset.
     */
    public function testQueryOffset()
    {
        $this->createPage('aaaa');
        $this->createPage('aaaa');
        $this->createPage('aaaa');
        $this->createPage('zzzz');
        $query = Query::create(Page::class, null, [], 3, 2);
        $results = $this->agent->query($query);
        $this->assertCount(1, $results);
        $first = $results->first();
        $this->assertEquals('zzzz', $first->title);
    }

    /**
     * It should order query results.
     */
    public function testQueryOrder()
    {
        $this->createPage('aaaa');
        $this->createPage('zzzz');
        $query = Query::create(Page::class, null, [
            'title' => 'desc',
        ]);
        $results = $this->agent->query($query);
        $first = $results->first();
        $this->assertEquals('zzzz', $first->title);
    }

    private function createPage($title = 'Hello World')
    {
    }
}
