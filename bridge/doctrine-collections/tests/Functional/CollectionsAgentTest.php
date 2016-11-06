<?php

declare(strict_types=1);

namespace Psi\Bridge\ObjectAgent\Doctrine\Orm\Tests\Functional;

use Psi\Bridge\ObjectAgent\Doctrine\Collections\CollectionsAgent;
use Psi\Bridge\ObjectAgent\Doctrine\Collections\Store;
use Psi\Component\ObjectAgent\Tests\Functional\AgentTestTrait;
use Psi\Component\ObjectAgent\Tests\Functional\Model\Page;

class CollectionsAgentTest extends \PHPUnit_Framework_TestCase
{
    private $agent;
    private $store;

    use AgentTestTrait;

    public function setUp()
    {
        $this->store = new Store([
            Page::class => [],
        ]);
        $this->agent = new CollectionsAgent($this->store);
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
