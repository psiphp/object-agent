<?php

namespace Psi\Bridge\ObjectAgent\Doctrine\Orm\Tests\Functional;

use Psi\Component\ObjectAgent\Query\Query;
use Psi\Component\ObjectAgent\Tests\Functional\Model\Page;

class OrmAgentTest extends OrmTestCase
{
    private $agent;
    private $entityManager;

    public function setUp()
    {
        $container = $this->getContainer();
        $this->agent = $container->get('psi_object_agent.doctrine_orm');
        $this->entityManager = $container->get('entity_manager');
        $this->initOrm($this->entityManager);
    }

    /**
     * It should find a entity.
     */
    public function testFind()
    {
        $page = $this->createPage();

        $entity = $this->agent->find(1, Page::class);
        $this->assertSame($page, $entity);
    }

    /**
     * It should throw an exception if no class argument is given to find.
     *
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage The "class" argument is mandatory for the doctrine ORM
     */
    public function testNoClass()
    {
        $this->agent->find(12);
    }

    /**
     * It should throw an exception if the entity was not found.
     *
     * @expectedException Psi\Component\ObjectAgent\Exception\ObjectNotFoundException
     * @expectedExceptionMessage Could not find entity with identifier "7" (class "Psi\Component\ObjectAgent\Tests\Functional\Model\Page")
     */
    public function testFindNotFound()
    {
        $this->agent->find(7, Page::class);
    }

    /**
     * It should save.
     */
    public function testSave()
    {
        $page = new Page();
        $page->id = 5;
        $this->agent->save($page);

        $entity = $this->entityManager->find(Page::class, 5);
        $this->assertNotNull($entity);
        $this->assertSame($page, $entity);
    }

    /**
     * It should delete.
     */
    public function testDelete()
    {
        $page = $this->createPage();
        $this->agent->delete($page);

        $entity = $this->entityManager->find(Page::class, 1);
        $this->assertNull($entity);
    }

    /**
     * It should return a entity's identifier (a UUID).
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
     * @expectedException \BadMethodCallException
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
        $this->entityManager->persist($page);
        $this->entityManager->flush();

        return $page;
    }
}
