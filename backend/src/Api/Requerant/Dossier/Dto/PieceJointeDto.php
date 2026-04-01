<?php

namespace MonIndemnisationJustice\Api\Requerant\Dossier\Dto;

use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\DocumentType;

class PieceJointeDto
{
    public function __construct(
        public readonly ?int $id,
        public string $chemin,
        public string $url,
        public string $nom,
        public string $mime,
        public int $taille,
        public DocumentType $type,
        public ?\DateTimeImmutable $dateAjout,
    ) {

    }

    public static function depuisDocument(Document $document): self
    {
        return new self(
            id: $document->getId(),
            chemin: $document->getFilename(),
            url: "/requerant/document/{$document->getId()}/{$document->getFilename()}",
            nom: $document->getOriginalFilename(),
            mime: $document->getMime(),
            taille: $document->getSize(),
            type: $document->getType(),
            dateAjout: $document->getDateDerniereModification(),
        );
    }
}
