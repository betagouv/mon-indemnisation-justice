<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Output;

use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\DocumentType;
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
        /** @var DocumentOutput[] */
        public readonly array $attestations
    ) {}

    public static function creerDepuisDossier(BrisPorte $dossier): self
    {
        return new self(
            id: $dossier->getId(),
            reference: $dossier->getReference(),
            requerant: $dossier->getRequerant()->getNomCourant(),
            adresse: $dossier->getAdresse()->getLibelle(),
            dateOperation: $dossier->getDateOperationPJ(),
            datePublication: $dossier->getDateDeclaration(),
            attestations: array_map(
                function (Document $document) {
                    $attestation = new DocumentOutput();
                    $attestation->id = $document->getId();
                    $attestation->filename = $document->getFilename();
                    $attestation->originalFilename = $document->getOriginalFilename();
                    $attestation->mime = $document->getMime();
                    $attestation->size = $document->getSize();
                    $attestation->estAjoutRequerant = $document->estAjoutRequerant();
                    $attestation->fileHash = $document->getFileHash();
                    $attestation->type = $document->getType()->value;

                    return $attestation;
                },
                $dossier->getDocumentsParType(DocumentType::TYPE_ATTESTATION_INFORMATION)
            )
        );
    }
}
