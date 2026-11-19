<?php

namespace MonIndemnisationJustice\Api\Requerant\Dossier\Dto;

use MonIndemnisationJustice\Entity\RequeteSignatureRequerant;
use Symfony\Component\Uid\Uuid;

readonly class RequeteSignature
{
    public function __construct(
        public Uuid $id,
        public string $urlExterne,
    ) {

    }

    public static function depuisRequeteSignature(RequeteSignatureRequerant $requeteSignature): self
    {
        return new self(
            id: $requeteSignature->getId(),
            urlExterne: $requeteSignature->getUrlSignatureExterne()
        );
    }
}
