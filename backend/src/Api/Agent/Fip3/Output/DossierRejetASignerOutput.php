<?php

namespace MonIndemnisationJustice\Api\Agent\Fip3\Output;

use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\DocumentType;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

class DossierRejetASignerOutput
{
    public function __construct(
        public readonly int $id,
        public readonly string $reference,
        public readonly string $requerant,
        #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'])]
        public readonly \DateTimeImmutable $dateDecision,
        public readonly string $agentDecision,
        public readonly ?string $motif = null,
    ) {}

    public static function creerDepuisDossier(BrisPorte $dossier): self
    {
        return new self(
            id: $dossier->getId(),
            reference: $dossier->getReference(),
            requerant: $dossier->getRequerant()->getNomCourant(),
            motif: $dossier->getDocumentParType(DocumentType::TYPE_COURRIER_MINISTERE)?->getMetaDonnee('contexte')['motifRefus'] ?? null,
            dateDecision: $dossier->getEtatDossier()->getDate(),
            agentDecision: $dossier->getEtatDossier()->getAgent()->getNomComplet()
        );
    }
}
