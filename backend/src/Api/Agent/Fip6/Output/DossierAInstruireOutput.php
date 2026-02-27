<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Output;

use MonIndemnisationJustice\Entity\Dossier;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

class DossierAInstruireOutput
{
    public function __construct(
        public readonly int $id,
        public readonly string $reference,
        public readonly string $requerant,
        public readonly string $adresse,
        #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
        public readonly \DateTimeInterface $dateOperation,
    ) {
    }

    public static function creerDepuisDossier(Dossier $dossier): self
    {
        return new self(
            id: $dossier->getId(),
            reference: $dossier->getReference(),
            requerant: $dossier->getUsager()->getNomCourant(),
            adresse: $dossier->getAdresse()->getLibelle(),
            dateOperation: $dossier->getDateOperationPJ()
        );
    }
}
