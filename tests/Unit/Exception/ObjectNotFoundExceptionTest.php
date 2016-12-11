<?php

declare(strict_types=1);

namespace Psi\Component\ObjectAgent\Tests\Unit\Exception;

use Psi\Component\ObjectAgent\Exception\ObjectNotFoundException;

class ObjectNotFoundExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * It should show the message for only an identifier.
     *
     * @expectedException Psi\Component\ObjectAgent\Exception\ObjectNotFoundException
     * @expectedExceptionMessage Could not find object with identifier "123"
     */
    public function testOnlyIdentifier()
    {
        throw ObjectNotFoundException::forClassAndIdentifier(null, 123);
    }

    /**
     * It should show the message for class and identifier.
     *
     * @expectedException Psi\Component\ObjectAgent\Exception\ObjectNotFoundException
     * @expectedExceptionMessage Could not find object of class "stdClass" with identifier "123"
     */
    public function testClassIdentifier()
    {
        throw ObjectNotFoundException::forClassAndIdentifier(\stdClass::class, 123);
    }
}
