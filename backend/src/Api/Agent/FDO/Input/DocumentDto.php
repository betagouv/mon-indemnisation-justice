<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Input;

use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\DocumentType;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\ObjectMapper\Condition\TargetClass;

#[Map(target: Document::class)]
#[Map(source: Document::class)]
final class DocumentDto
{
    public int $id;
    public string $filename;
    public string $originalFilename;
    public string $mime;
    public int $size;

    #[Map(if: new TargetClass(Document::class), target: 'setAjoutRequerant')]
    public ?bool $estAjoutRequerant = false;

    public DocumentType $type;

    public ?\DateTimeInterface $dateDerniereModification = null;
}
