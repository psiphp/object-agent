<?php

namespace Psi\Component\ObjectAgent\Tests\Functional\Model;

use Psi\Component\ObjectAgent\Tests\Functional\Model\Page;

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

