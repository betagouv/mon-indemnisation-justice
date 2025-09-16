<?php

namespace MonIndemnisationJustice\Api\Agent\Fip3\Output;

use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\EtatDossierType;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

class DossierEnAttenteIndemnisationOutput
{
    public function __construct(
        public readonly int $id,
        public readonly string $reference,
        public readonly string $requerant,
        public readonly float $montantIndemnisation,
        #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
        public readonly \DateTimeImmutable $dateTransmission,
    ) {}

    public static function creerDepuisDossier(BrisPorte $dossier): self
    {
        $etatValidation = $dossier->getEtat(EtatDossierType::DOSSIER_OK_EN_ATTENTE_PAIEMENT);

        return new self(
            id: $dossier->getId(),
            reference: $dossier->getReference(),
            requerant: $dossier->getRequerant()->getNomCourant(),
            montantIndemnisation: $dossier->getMontantIndemnisation(),
            dateTransmission: $etatValidation->getDate(),
        );
    }
}
