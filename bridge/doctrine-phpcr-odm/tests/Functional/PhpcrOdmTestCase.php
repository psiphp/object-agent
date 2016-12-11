<?php

declare(strict_types=1);

namespace Psi\Bridge\ObjectAgent\Doctrine\PhpcrOdm\Tests\Functional;

use Doctrine\ODM\PHPCR\DocumentManagerInterface;
use PhpBench\DependencyInjection\Container;

class PhpcrOdmTestCase extends \PHPUnit_Framework_TestCase
{
    protected function getContainer()
    {
        $container = new Container([
            \Psi\Bridge\ObjectAgent\Doctrine\PhpcrOdm\Tests\Functional\PhpcrOdmExtension::class,
        ], []);
        $container->init();

        return $container;
    }

    protected function initPhpcr(DocumentManagerInterface $documentManager)
    {
        $session = $documentManager->getPhpcrSession();

        $rootNode = $session->getRootNode();
        if ($rootNode->hasNode('test')) {
            $rootNode->getNode('test')->remove();
        }

        $rootNode->addNode('test');
    }
}
