<?php

namespace MonIndemnisationJustice\Api\Requerant\Brouillon\Dto;

use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\DocumentType;
use Symfony\Component\ObjectMapper\Attribute\Map;

#[Map(source: Document::class)]
class PieceJointeDto
{
    public ?int $id;
    #[Map(source: 'filename')]
    public ?string $chemin;
    #[Map(source: 'originalFilename')]
    public ?string $nom;
    public ?string $mime;
    #[Map(source: 'size')]
    public ?int $taille;
    public DocumentType $type;
}
