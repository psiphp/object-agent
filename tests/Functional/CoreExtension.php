<?php

declare(strict_types=1);

namespace Psi\Bridge\ObjectAgent\Doctrine\PhpcrOdm\Tests\Functional;

use PhpBench\DependencyInjection\Container;
use PhpBench\DependencyInjection\ExtensionInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class CoreExtension implements ExtensionInterface
{
    public function getDefaultConfig()
    {
        return [
        ];
    }

    public function load(Container $container)
    {
        $this->register('event_dispatcher', function (Container $container) {
            return new EventDispatcher();
        });
    }
}
