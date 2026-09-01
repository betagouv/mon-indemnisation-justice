<?php

namespace MonIndemnisationJustice\Api\Requerant\Dossier\Dto;

use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\DossierType;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

class DossierApercuDto
{
    public function __construct(
        public int $id,
        public ?string $reference,
        public DossierType $type,
        public ?EtatDossierDto $etatActuel = null,
        #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
        public ?\DateTimeImmutable $dateDepot = null,
    ) {
    }

    public static function depuisDossier(Dossier $dossier): self
    {
        return new self(
            id: $dossier->getId(),
            reference: $dossier->getReference(),
            type: $dossier->getType(),
            etatActuel: EtatDossierDto::depuisEtatDossier($dossier->getEtatDossier()),
            dateDepot: $dossier->getDateDepot() ? \DateTimeImmutable::createFromInterface($dossier->getDateDepot()) : null,
        );
    }
}
