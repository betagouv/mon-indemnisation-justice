<?php

namespace MonIndemnisationJustice\Api\Agent\Resources\Output;

use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\DocumentType;
use Symfony\Component\ObjectMapper\Attribute\Map;

#[Map(source: Document::class)]
final class DocumentOutput
{
    public int $id;
    public string $filename;
    public string $originalFilename;
    public string $mime;
    public int $size;

    public ?bool $estAjoutRequerant;
    public string $corps;
    public string $fileHash;
    #[Map(transform: [DocumentType::class, 'toString'])]
    public string $type;
    public mixed $metaDonnees;
}
