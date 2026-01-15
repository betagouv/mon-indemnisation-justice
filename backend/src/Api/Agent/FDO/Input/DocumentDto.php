<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Input;

use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\DocumentType;
use Symfony\Component\ObjectMapper\Attribute\Map;

#[Map(target: Document::class)]
#[Map(source: Document::class)]
final class DocumentDto
{
    public int $id;
    public string $filename;
    public string $originalFilename;
    public string $mime;
    public int $size;

    public ?bool $estAjoutRequerant;
    #[Map(transform: [DocumentType::class, 'toString'])]
    public string $type;
    public ?\DateTimeInterface $dateDerniereModification = null;
}
