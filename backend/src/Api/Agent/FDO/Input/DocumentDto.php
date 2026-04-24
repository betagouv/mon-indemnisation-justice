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

    public function versDocument(): Document
    {
        return new Document()
            ->setId($this->id)
            ->setType($this->type)
            ->setFilename($this->filename)
            ->setOriginalFilename($this->originalFilename)
            ->setMime($this->id)
            ->setSize($this->size)
            ->setAjoutRequerant($this->estAjoutRequerant)
            ->setDateDerniereModification($this->dateDerniereModification);
    }
}
