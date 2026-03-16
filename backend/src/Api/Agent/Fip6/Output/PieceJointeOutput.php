<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Output;

use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\DocumentType;

final class PieceJointeOutput
{
    public function __construct(
        public readonly int $id,
        public readonly string $filename,
        public readonly string $originalFilename,
        public readonly string $mime,
        public readonly int $size,
        public readonly ?bool $estAjoutRequerant,
        public readonly ?string $corps,
        public readonly string $fileHash,
        public readonly DocumentType $type,
        public readonly mixed $metaDonnees,
        public readonly ?\DateTimeInterface $dateDerniereModification = null,
    ) {

    }

    public static function depuisDocument(Document $document): self
    {
        return new self(
            id: $document->getId(),
            filename: $document->getFilename(),
            originalFilename: $document->getOriginalFilename(),
            mime: $document->getMime(),
            size: $document->getSize(),
            estAjoutRequerant: $document->estAjoutRequerant(),
            corps: $document->getCorps(),
            fileHash: $document->getFileHash(),
            type: $document->getType(),
            metaDonnees: $document->getMetaDonnees(),
            dateDerniereModification: $document->getDateDerniereModification(),
        );
    }
}
