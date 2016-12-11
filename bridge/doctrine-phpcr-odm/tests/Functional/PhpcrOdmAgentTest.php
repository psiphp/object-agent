<?php

declare(strict_types=1);

namespace Psi\Bridge\ObjectAgent\Doctrine\PhpcrOdm\Tests\Functional;

use Psi\Bridge\ObjectAgent\Doctrine\PhpcrOdm\Tests\Functional\Model\Article;
use Psi\Component\ObjectAgent\Tests\Functional\AgentTestTrait;
use Psi\Component\ObjectAgent\Tests\Functional\Model\Page;

class PhpcrOdmAgentTest extends PhpcrOdmTestCase
{
    private $agent;
    private $documentManager;

    use AgentTestTrait;

    public function setUp()
    {
        $container = $this->getContainer();
        $this->agent = $container->get('psi_object_agent.phpcr_odm');
        $this->documentManager = $container->get('phpcr_odm');
        $this->initPhpcr($this->documentManager);
    }

    /**
     * It should set the parent document on a given document.
     */
    public function testSetParent()
    {
        $parent = $this->createPage();
        $article = new Article();
        $article->name = 'article';
        $this->agent->setParent($article, $parent);

        $this->documentManager->persist($article);
        $this->documentManager->flush();

        $document = $this->documentManager->find(null, '/test/page-1/article');
        $this->assertNotNull($document);
    }

    /**
     * It should throw an exception if attempting to set parent on a document with no parent mapping.
     *
     * @expectedException \RuntimeException
     * @expectedExceptionMessage does not have a ParentDocument mapping
     */
    public function testSetParentNoParentMapping()
    {
        $parent = $this->createPage();
        $page = new Page();
        $this->agent->setParent($page, $parent);
    }

    private function createPage($title = 'Hello World')
    {
        static $id = 1;

        $page = new Page();
        $page->title = $title;
        $page->path = '/test/page-' . $id++;
        $this->documentManager->persist($page);
        $this->documentManager->flush();

        return $page;
    }
}
