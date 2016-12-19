<?php

declare(strict_types=1);

namespace Psi\Bridge\ObjectAgent\Doctrine\Orm\Tests\Functional;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use PhpBench\DependencyInjection\Container;

abstract class OrmTestCase extends \PHPUnit_Framework_TestCase
{
    protected function getContainer()
    {
        $container = new Container([
            \Psi\Bridge\ObjectAgent\Doctrine\Orm\Tests\Functional\OrmExtension::class,
        ], []);
        $container->init();

        return $container;
    }

    protected function initOrm(EntityManagerInterface $entityManager)
    {
        $schemaTool = new SchemaTool($entityManager);
        $metadatas = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadatas);
        $schemaTool->createSchema($metadatas);
    }
}
