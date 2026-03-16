<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Output;

use MonIndemnisationJustice\Entity\RapportAuLogement;
use MonIndemnisationJustice\Entity\TypeAttestation;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

readonly class BaseDossierOutput
{
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'])]
    public ?\DateTime $dateDepot;

    public function __construct(
        public int $id,
        public ?string $reference,
        public EtatDossierOutput $etat,
        ?\DateTime $dateDepot,
        public ?string $montantIndemnisation,
        public ?int $redacteur,
        public ?TypeAttestation $typeAttestation,
        // TODO renommer en front également
        public ?RapportAuLogement $qualiteRequerant,
        public ?bool $estEligible,
    ) {
        $this->dateDepot = $dateDepot;
    }
}
