<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Output;

use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Entity\Dossier;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

class DossierACategoriserOutput
{
    public function __construct(
        public readonly int $id,
        public readonly string $reference,
        public readonly string $requerant,
        public readonly string $adresse,
        #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
        public readonly ?\DateTimeInterface $dateOperation,
        #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'])]
        public readonly \DateTimeInterface $datePublication,
        /** @var PieceJointeOutput[] */
        public readonly array $attestations,
    ) {
    }

    public static function depuisDossier(Dossier $dossier): self
    {
        return new self(
            id: $dossier->getId(),
            reference: $dossier->getReference(),
            requerant: $dossier->getUsager()->getNomCourant(),
            adresse: $dossier->getBrisPorte()->getAdresse()->getLibelle(),
            dateOperation: $dossier->getBrisPorte()->getDateOperation(),
            datePublication: $dossier->getDateDeclaration(),
            attestations: array_map(
                fn (Document $document) => PieceJointeOutput::depuisDocument($document),
                $dossier->getDocumentsParType(DocumentType::TYPE_ATTESTATION_INFORMATION)
            )
        );
    }
}
