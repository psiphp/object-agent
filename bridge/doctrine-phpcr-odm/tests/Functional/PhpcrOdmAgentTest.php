<?php

namespace Psi\Bridge\ObjectAgent\Doctrine\PhpcrOdm\Tests\Functional;

use Psi\Bridge\ObjectAgent\Doctrine\PhpcrOdm\Tests\Functional\Model\Article;
use Psi\Bridge\ObjectAgent\Doctrine\PhpcrOdm\Tests\Functional\Model\PageNoUuid;
use Psi\Component\ObjectAgent\Query\Query;
use Psi\Component\ObjectAgent\Tests\Functional\Model\Page;

class PhpcrOdmAgentTest extends PhpcrOdmTestCase
{
    private $agent;
    private $documentManager;

    public function setUp()
    {
        $container = $this->getContainer();
        $this->agent = $container->get('psi_object_agent.phpcr_odm');
        $this->documentManager = $container->get('phpcr_odm');
        $this->initPhpcr($this->documentManager);
    }

    /**
     * It should find a document.
     */
    public function testFind()
    {
        $page = $this->createPage();

        $document = $this->agent->find('/test/foobar');
        $this->assertSame($page, $document);
    }

    /**
     * It should throw an exception if the document was not found.
     *
     * @expectedException Psi\Component\ObjectAgent\Exception\ObjectNotFoundException
     * @expectedExceptionMessage Could not find document with identifier "/test/foobar" (class "<null>")
     */
    public function testFindNotFound()
    {
        $this->agent->find('/test/foobar');
    }

    /**
     * It should save.
     */
    public function testSave()
    {
        $page = new Page();
        $page->path = '/test/new';
        $this->agent->save($page);

        $document = $this->documentManager->find(null, '/test/new');
        $this->assertNotNull($document);
        $this->assertSame($page, $document);
    }

    /**
     * It should delete.
     */
    public function testDelete()
    {
        $page = $this->createPage();
        $this->agent->delete($page);

        $document = $this->documentManager->find(null, '/test/foobar');
        $this->assertNull($document);
    }

    /**
     * It should return a document's identifier (a UUID).
     */
    public function testGetIdentifier()
    {
        $page = $this->createPage();
        $identifier = $this->agent->getIdentifier($page);
        $this->assertNotNull($identifier);
    }

    /**
     * It should throw an exception if the document does not have a mapped UUID field.
     *
     * @expectedException \RuntimeException
     * @expectedExceptionMessage does not have a UUID-mapped property
     */
    public function testGetIdentifierNoMappedUuid()
    {
        $page = new PageNoUuid();
        $page->path = '/test/foobar';

        $identifier = $this->agent->getIdentifier($page);
        $this->assertNotNull($identifier);
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

        $document = $this->documentManager->find(null, '/test/foobar/article');
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

    /**
     * It should say if it supports a given object.
     */
    public function testSupports()
    {
        $this->assertTrue($this->agent->supports(Article::class));
        $this->assertFalse($this->agent->supports(\stdClass::class));
    }

    public function testQuery()
    {
        $this->createPage('Hello');
        $this->createPage('Foobar', '/test/barbar');
        $query = Query::create(Page::class, Query::composite(
            'and',
            Query::comparison('eq', 'title', 'Hello')
        ));
        $results = $this->agent->query($query);
        $this->assertCount(1, $results);
    }

    private function createPage($title = 'Hello World', $id = '/test/foobar')
    {
        $page = new Page();
        $page->title = $title;
        $page->path = $id;
        $this->documentManager->persist($page);
        $this->documentManager->flush();

        return $page;
    }
}
