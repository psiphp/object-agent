<?php

declare(strict_types=1);

namespace Psi\Component\ObjectAgent\Tests\Functional\Model;

class Comment
{
    public $id;
    public $title;
    public $page;

    public function __construct(Page $page, string $title = null)
    {
        $this->page = $page;
        $this->title = $title;
    }
}
