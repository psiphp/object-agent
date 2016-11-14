<?php

namespace Psi\Bridge\ObjectAgent\Doctrine\Orm\Tests\Functional;

use Psi\Component\ObjectAgent\Tests\Functional\AgentTestTrait;
use Psi\Component\ObjectAgent\Tests\Functional\Model\Page;

class OrmAgentTest extends OrmTestCase
{
    private $agent;
    private $entityManager;

    use AgentTestTrait;

    public function setUp()
    {
        $container = $this->getContainer();
        $this->agent = $container->get('psi_object_agent.doctrine_orm');
        $this->entityManager = $container->get('entity_manager');
        $this->initOrm($this->entityManager);
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
