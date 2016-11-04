<?php

namespace Psi\Bridge\ObjectAgent\Doctrine\Orm\Tests\Functional;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use PhpBench\DependencyInjection\Container;
use PhpBench\DependencyInjection\ExtensionInterface;
use Psi\Bridge\ObjectAgent\Doctrine\Orm\OrmAgent;

class OrmExtension implements ExtensionInterface
{
    public function getDefaultConfig()
    {
        return [
            'db_path' => __DIR__ . '/../../../../cache/test.sqlite',
        ];
    }

    public function load(Container $container)
    {
        $container->register('entity_manager', function (Container $container) {
            $dbParams = [
                'driver'    => 'pdo_sqlite',
                'path' => $container->getParameter('db_path'),
            ];
            $paths = [
                __DIR__ . '/mappings',
            ];
            $config = Setup::createXMLMetadataConfiguration($paths, true);
            $manager = EntityManager::create($dbParams, $config);

            return $manager;
        });

        $container->register('psi_object_agent.doctrine_orm', function (Container $container) {
            return new OrmAgent($container->get('entity_manager'));
        });
    }
}
