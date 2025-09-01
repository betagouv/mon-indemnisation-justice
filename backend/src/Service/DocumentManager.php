<?php

namespace MonIndemnisationJustice\Service;

use MonIndemnisationJustice\Entity\Document;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\DependencyInjection\Attribute\Target;

class DocumentManager
{
    public function __construct(
        #[Target('default.storage')]
        protected  readonly FilesystemOperator $storage,
    ) {}

    public function getDocumentBody(Document $document): ?string
    {
        return $this->storage->read($document->getFilename());
    }
}