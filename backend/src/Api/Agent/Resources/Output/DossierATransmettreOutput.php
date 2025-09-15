<?php

namespace MonIndemnisationJustice\Api\Agent\Resources\Output;

use MonIndemnisationJustice\Entity\BrisPorte;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

class DossierATransmettreOutput
{
    public function __construct(
        public readonly int $id,
        public readonly string $reference,
        public readonly string $requerant,
        public readonly float $montantIndemnisation,
        #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
        public readonly \DateTimeImmutable $dateValidation,
        public readonly string $agentValidateur,
    ) {}

    public static function creerDepuisDossier(BrisPorte $dossier): self
    {
        return new self(
            id: $dossier->getId(),
            reference: $dossier->getReference(),
            requerant: $dossier->getRequerant()->getNomCourant(),
            montantIndemnisation: $dossier->getMontantIndemnisation(),
            dateValidation: $dossier->getEtatDossier()->getDate(),
            agentValidateur: $dossier->getEtatDossier()->getAgent()?->getNomComplet()
        );
    }
}
