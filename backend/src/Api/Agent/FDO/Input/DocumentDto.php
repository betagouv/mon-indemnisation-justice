<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Input;

use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\DocumentType;

final class DocumentDto
{
    public int $id;
    public string $filename;
    public string $originalFilename;
    public string $mime;
    public int $size;
    public ?bool $estAjoutRequerant = false;
    public string $fileHash;

    public DocumentType $type;

    public ?\DateTimeInterface $dateDerniereModification = null;

    public static function depuisDocument(Document $document): self
    {
        $dto = new self();
        $dto->id = $document->getId();
        $dto->filename = $document->getFilename();
        $dto->originalFilename = $document->getOriginalFilename();
        $dto->mime = $document->getMime();
        $dto->size = $document->getSize();
        $dto->estAjoutRequerant = $document->estAjoutRequerant();
        $dto->fileHash = $document->getFileHash();
        $dto->type = $document->getType();
        $dto->dateDerniereModification = $document->getDateDerniereModification();

        return $dto;
    }
}
