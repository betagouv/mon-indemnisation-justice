<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Output;

use MonIndemnisationJustice\Entity\Dossier;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

class DossierPropositionASignerOutput
{
    public function __construct(
        public readonly int $id,
        public readonly string $reference,
        public readonly string $requerant,
        public readonly float $montantIndemnisation,
        #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'])]
        public readonly \DateTimeImmutable $dateDecision,
        public readonly string $agentDecision,
    ) {
    }

    public static function creerDepuisDossier(Dossier $dossier): self
    {
        return new self(
            id: $dossier->getId(),
            reference: $dossier->getReference(),
            requerant: $dossier->getUsager()->getNomCourant(),
            montantIndemnisation: $dossier->getMontantIndemnisation(),
            dateDecision: $dossier->getEtatDossier()->getDate(),
            agentDecision: $dossier->getEtatDossier()->getAgent()->getNomComplet()
        );
    }
}
