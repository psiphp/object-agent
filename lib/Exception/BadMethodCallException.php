<?php

namespace Psi\Component\ObjectAgent\Exception;

class BadMethodCallException extends \BadMethodCallException
{
    public static function classArgumentIsMandatory(string $implementation)
    {
        throw new self(sprintf(
            'The class argument is mandatory for the %s agent', $implementation
        ));
    }

    public static function setParentNotSupported(string $implementation)
    {
        throw new self(sprintf(
            'setParent is not supported by the %s agent',
            $implementation
        ));
    }

    public static function queryCountNotSupported(string $implementation)
    {
        throw new self(sprintf(
            'queryCount is not supported by the %s agent',
            $implementation
        ));
    }

    public static function comparisonNotSupported(string $comparison)
    {
        throw new self(sprintf(
            'Comparison "%s" is not supported.',
             $comparison
        ));
    }
}
