<?php

declare(strict_types=1);

namespace Psi\Component\ObjectAgent\Tests\Functional\Model;

class Page
{
    public $id;
    public $title;
    public $path;

    public function __construct(string $title = null)
    {
        $this->title = $title;
    }
}
