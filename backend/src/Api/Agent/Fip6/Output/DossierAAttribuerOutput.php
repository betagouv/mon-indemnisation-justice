<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Output;

use MonIndemnisationJustice\Entity\BrisPorte;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

class DossierAAttribuerOutput
{
    public function __construct(
        public readonly int $id,
        public readonly string $reference,
        public readonly string $requerant,
        public readonly ?string $adresse,
        #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
        public readonly ?\DateTimeInterface $dateOperation,
        #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'])]
        public readonly \DateTimeInterface $datePublication,
    ) {}

    public static function creerDepuisDossier(BrisPorte $dossier): self
    {
        return new self(
            id: $dossier->getId(),
            reference: $dossier->getReference(),
            requerant: $dossier->getRequerant()->getNomCourant(),
            adresse: !empty($dossier->getAdresse()->getLigne1()) ? $dossier->getAdresse()->getLibelle() : null,
            dateOperation: $dossier->getDateOperationPJ(),
            datePublication: $dossier->getDateDeclaration()
        );
    }
}
