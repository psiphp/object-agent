<?php

namespace Psi\Component\ObjectAgent\Agent\Doctrine\Event;

use Doctrine\ODM\PHPCR\DocumentManagerInterface;
use Psi\Component\ObjectAgent\Event\AbstractObjectEvent;

class ObjectEvent extends AbstractObjectEvent
{
    private $documentManager;
    private $object;

    public function __construct(DocumentManagerInterface $documentManager, $object)
    {
        $this->documentManager = $documentManager;
        $this->object = $object;
    }

    public function getDocumentManager(): DocumentManagerInterface
    {
        return $this->documentManager;
    }

    public function getObject()
    {
        return $this->object;
    }
}
