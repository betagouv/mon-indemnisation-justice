<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Output;

use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorteErreurType;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Uid\Uuid;

class DeclarationFDOBrisPorteOutput
{
    public function __construct(
        public readonly Uuid $id,
        public readonly ?string $reference = null,
        #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
        public readonly ?\DateTimeInterface $dateOperation = null,
        public ?DeclarationFDOBrisPorteErreurType $estErreur = null,
        public ?string $descriptionErreur = null,

        // #[Map(target: AdresseOutput::class)]
        public ?AdresseOutput $adresse = null,

        // #[Map(target: CoordonneesRequerantOutput::class)]
        public ?CoordonneesRequerantOutput $coordonneesRequerant = null,
        public ?string $precisionsRequerant = null,

        // #[Map(target: ProcedureJudiciaireOutput::class)]
        public ?ProcedureJudiciaireOutput $procedure = null,

        // #[Map(target: AgentOutput::class)]
        public ?AgentOutput $agent = null,
        #[Context([DateTimeNormalizer::FORMAT_KEY => \DateTimeInterface::W3C])]
        public ?\DateTimeInterface $dateCreation = null,
        #[Context([DateTimeNormalizer::FORMAT_KEY => \DateTimeInterface::W3C])]
        public ?\DateTimeInterface $dateSoumission = null,
        public array $piecesJointes = [],
    ) {
    }

    public static function depuisDeclarationFDO(DeclarationFDOBrisPorte $declarationFDOBrisPorte): self
    {
        return new self(
            id: $declarationFDOBrisPorte->getId(),
            reference: $declarationFDOBrisPorte->getReference(),
            dateOperation: $declarationFDOBrisPorte->getDateOperation(),
            estErreur: $declarationFDOBrisPorte->getEstErreur(),
            descriptionErreur: $declarationFDOBrisPorte->getDescriptionErreur(),
            adresse: $declarationFDOBrisPorte->getAdresse() ? AdresseOutput::depuisAdresse($declarationFDOBrisPorte->getAdresse()) : null,
            coordonneesRequerant: $declarationFDOBrisPorte->getCoordonneesRequerant() ? CoordonneesRequerantOutput::depuisCoordonneesRequerant($declarationFDOBrisPorte->getCoordonneesRequerant()) : null,
            precisionsRequerant: $declarationFDOBrisPorte->getPrecisionsRequerant(),
            procedure: $declarationFDOBrisPorte->getProcedure() ? ProcedureJudiciaireOutput::depuisProcedureJudiciaire($declarationFDOBrisPorte->getProcedure()) : null,
            agent: AgentOutput::depuisAgent($declarationFDOBrisPorte->getAgent()),
            dateCreation: $declarationFDOBrisPorte->getDateCreation(),
            dateSoumission: $declarationFDOBrisPorte->getDateSoumission(),
        );
    }
}
