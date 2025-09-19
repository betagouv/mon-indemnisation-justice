<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Output;

use MonIndemnisationJustice\Entity\BrisPorte;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

class DossierArreteASignerOutput
{
    public function __construct(
        public readonly int $id,
        public readonly string $reference,
        public readonly string $requerant,
        public readonly float $montantIndemnisation,
        #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'])]
        public readonly \DateTimeImmutable $dateEdition,
        public readonly string $agentEdition,
    ) {}

    public static function creerDepuisDossier(BrisPorte $dossier): self
    {
        return new self(
            id: $dossier->getId(),
            reference: $dossier->getReference(),
            requerant: $dossier->getRequerant()->getNomCourant(),
            montantIndemnisation: $dossier->getMontantIndemnisation(),
            dateEdition: $dossier->getEtatDossier()->getDate(),
            agentEdition: $dossier->getEtatDossier()->getAgent()->getNomComplet()
        );
    }
}
