<?php

namespace MonIndemnisationJustice\Api\Requerant\Dossier\Dto;

use MonIndemnisationJustice\Entity\RequeteSignatureRequerant;
use Symfony\Component\Uid\Uuid;

readonly class RequeteSignature
{
    public function __construct(
        public Uuid $id,
        public ?string $urlExterne = null,
        public ?\DateTimeImmutable $dateActivation = null,
        public ?\DateTimeImmutable $dateSignature = null,
        public ?\DateTimeImmutable $dateAnnulation = null,
        public ?\DateTimeImmutable $dateExpiration = null,
    ) {

    }

    public static function depuisRequeteSignature(RequeteSignatureRequerant $requeteSignature): self
    {
        return new self(
            id: $requeteSignature->getId(),
            urlExterne: $requeteSignature->getUrlSignatureExterne(),
            dateActivation: $requeteSignature->getDateActivation(),
            dateSignature: $requeteSignature->getDateSignature(),
            dateAnnulation: $requeteSignature->getDateAnnulation(),
            dateExpiration: $requeteSignature->getDateExpiration(),
        );
    }
}
