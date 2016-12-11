<?php

declare(strict_types=1);

namespace Psi\Component\ObjectAgent\Exception;

class ObjectNotFoundException extends \RuntimeException
{
    public static function forClassAndIdentifier($class, $identifier)
    {
        if (null === $class) {
            return new static(sprintf(
                'Could not find object with identifier "%s"',
                $identifier
            ));
        }

        return new static(sprintf(
            'Could not find object of class "%s" with identifier "%s"',
            $class, $identifier
        ));
    }
}
