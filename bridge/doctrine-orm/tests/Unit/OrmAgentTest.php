<?php

declare(strict_types=1);

namespace Psi\Bridge\ObjectAgent\Doctrine\Orm\Tests\Unit;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Psi\Bridge\ObjectAgent\Doctrine\Orm\OrmAgent;

class OrmAgentTest extends \PHPUnit_Framework_TestCase
{
    private $agent;

    public function setUp()
    {
        $this->entityManager = $this->prophesize(EntityManagerInterface::class);
        $this->agent = new OrmAgent(
            $this->entityManager->reveal()
        );

        $this->metadataFactory = $this->prophesize(ClassMetadataFactory::class);
        $this->classMetadata = $this->prophesize(ClassMetadata::class);
    }

    /**
     * It should throw an exception if an entity has multiple primary keys.
     *
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Only objects with a single primary key are supported. Class: "stdClass", primary key fields: "one", "two"
     */
    public function testFindManyManyPks()
    {
        $this->entityManager->getMetadataFactory()->willReturn($this->metadataFactory->reveal());
        $this->metadataFactory->getMetadataFor(\stdClass::class)->willReturn(
            $this->classMetadata->reveal()
        );
        $this->classMetadata->getIdentifier()->willReturn(['one', 'two']);
        $this->agent->findMany(['123', '456'], \stdClass::class);
    }
}
