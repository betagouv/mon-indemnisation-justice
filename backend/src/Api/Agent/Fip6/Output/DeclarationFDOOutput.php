<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Output;

use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorteErreurType;
use MonIndemnisationJustice\Entity\Document;

class DeclarationFDOOutput
{
    public function __construct(
        public readonly string $id,
        public readonly \DateTime $dateCreation,
        public readonly DeclarationFDOBrisPorteErreurType $estErreur,
        public readonly ?string $descriptionErreur,
        public readonly \DateTime $dateOperation,
        public readonly \DateTime $dateSoumission,
        public readonly AdresseOutput $adresse,
        public readonly AgentOutput $agent,
        public readonly bool $enPresenceRequerant,
        public readonly string $precisionsRequerant,
        public readonly CoordonneesRequerantOutput $coordonneesRequerant,
        public readonly ProcedureJudiciaireOutput $procedure,
        /** @var PieceJointeOutput[] $piecesJointes */
        public readonly array $piecesJointes,
    ) {

    }

    public static function depuisDeclarationFDOBrisPorte(?DeclarationFDOBrisPorte $declarationFDOBrisPorte): ?self
    {
        if (null === $declarationFDOBrisPorte) {
            return null;
        }

        return new self(
            id: $declarationFDOBrisPorte->getId()->toString(),
            dateCreation: \DateTime::createFromInterface($declarationFDOBrisPorte->getDateCreation()),
            estErreur: $declarationFDOBrisPorte->getEstErreur(),
            descriptionErreur: $declarationFDOBrisPorte->getDescriptionErreur(),
            dateOperation: \DateTime::createFromInterface($declarationFDOBrisPorte->getDateOperation()),
            dateSoumission: \DateTime::createFromInterface($declarationFDOBrisPorte->getDateSoumission()),
            adresse: AdresseOutput::depuisAdresse($declarationFDOBrisPorte->getAdresse()),
            agent: AgentOutput::depuisAgent($declarationFDOBrisPorte->getAgent()),
            enPresenceRequerant: null !== $declarationFDOBrisPorte->getCoordonneesRequerant(),
            precisionsRequerant: $declarationFDOBrisPorte->getPrecisionsRequerant(),
            coordonneesRequerant: CoordonneesRequerantOutput::depuisCoordonneesRequerant($declarationFDOBrisPorte->getCoordonneesRequerant()),
            procedure: ProcedureJudiciaireOutput::depuisProcedureJudiciaire($declarationFDOBrisPorte->getProcedure()),
            piecesJointes: $declarationFDOBrisPorte->getPiecesJointes()->map(fn (Document $document) => PieceJointeOutput::depuisDocument($document))->toArray(),
        );
    }
}
