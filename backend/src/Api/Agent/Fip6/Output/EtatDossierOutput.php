<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Output;

use MonIndemnisationJustice\Entity\EtatDossier;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

#[Map(source: EtatDossier::class)]
final readonly class EtatDossierOutput
{
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'])]
    public \DateTimeInterface $dateEntree;

    public function __construct(
        public int $id,
        public string $etat,
        \DateTimeInterface $dateEntree,
        public ?int $redacteur = null,
        public bool $requerant = false,
        public ?array $contexte = null,
    ) {
        $this->dateEntree = $dateEntree;
    }

    public static function depuisEtatDossier(EtatDossier $etatDossier): self
    {
        return new self(
            id: $etatDossier->getId(),
            etat: $etatDossier->getEtat()->value,
            dateEntree: $etatDossier->getDateEntree(),
            redacteur: $etatDossier->getAgent()?->getId(),
            requerant: null !== $etatDossier->getRequerant(),
            contexte: $etatDossier->getContexte(),
        );
    }
}
