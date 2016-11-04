<?php

namespace Psi\Bridge\ObjectAgent\Doctrine\PhpcrOdm\Tests\Functional;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\DBAL\DriverManager;
use Doctrine\ODM\PHPCR\Configuration;
use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\Mapping\Driver\AnnotationDriver;
use Doctrine\ODM\PHPCR\Mapping\Driver\XmlDriver;
use Doctrine\ODM\PHPCR\NodeTypeRegistrator;
use Jackalope\RepositoryFactoryDoctrineDBAL;
use Jackalope\Transport\DoctrineDBAL\RepositorySchema;
use PhpBench\DependencyInjection\Container;
use PhpBench\DependencyInjection\ExtensionInterface;
use PHPCR\SimpleCredentials;
use Psi\Bridge\ObjectAgent\Doctrine\PhpcrOdm\PhpcrOdmAgent;

class PhpcrOdmExtension implements ExtensionInterface
{
    public function getDefaultConfig()
    {
        return [
            'db_path' => __DIR__ . '/../../../../cache/test.sqlite',
        ];
    }

    public function load(Container $container)
    {
        $container->register('phpcr_odm', function (Container $container) {
            $dbPath = $container->getParameter('db_path');
            $registerNodeTypes = false;

            $connection = DriverManager::getConnection([
                'driver'    => 'pdo_sqlite',
                'path' => $dbPath,
            ]);

            // automatically setup the schema if the db doesn't exist yet.
            if (!file_exists($dbPath)) {
                if (!file_exists($dir = dirname($dbPath))) {
                    mkdir($dir);
                }

                $schema = new RepositorySchema();
                foreach ($schema->toSql($connection->getDatabasePlatform()) as $sql) {
                    $connection->exec($sql);
                }

                $registerNodeTypes = true;
            }

            $factory = new RepositoryFactoryDoctrineDBAL();
            $repository = $factory->getRepository([
                'jackalope.doctrine_dbal_connection' => $connection,
            ]);
            $session = $repository->login(new SimpleCredentials(null, null), 'default');

            if ($registerNodeTypes) {
                $typeRegistrator = new NodeTypeRegistrator();
                $typeRegistrator->registerNodeTypes($session);
            }

            $xmlDriver = new XmlDriver([__DIR__ . '/mappings']);
            $annotationDriver = new AnnotationDriver(new AnnotationReader(), [
                __DIR__ . '/../../../vendor/doctrine/phpcr-odm/lib/Doctrine/ODM/PHPCR/Document',
            ]);
            $chain = new MappingDriverChain();
            $chain->addDriver($xmlDriver, 'Psi\Component\ObjectAgent\Tests\Functional\Model');
            $chain->addDriver($xmlDriver, 'Psi\Bridge\ObjectAgent\Doctrine\PhpcrOdm\Tests\Functional\Model');
            $chain->addDriver($annotationDriver, 'Doctrine');

            $config = new Configuration();
            $config->setMetadataDriverImpl($chain);

            return DocumentManager::create($session, $config);
        });

        $container->register('psi_object_agent.phpcr_odm', function (Container $container) {
            return new PhpcrOdmAgent($container->get('phpcr_odm'));
        });
    }
}
