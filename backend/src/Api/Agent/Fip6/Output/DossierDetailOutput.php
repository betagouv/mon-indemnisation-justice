<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Output;

use MonIndemnisationJustice\Entity\AdministrationType;
use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\RapportAuLogement;
use MonIndemnisationJustice\Entity\TypeAttestation;

readonly class DossierDetailOutput extends BaseDossierOutput
{
    public function __construct(
        int $id,
        ?string $reference,
        EtatDossierOutput $etat,
        ?\DateTimeImmutable $dateDepot,
        ?string $montantIndemnisation,
        ?RedacteurOutput $redacteur,
        ?TypeAttestation $typeAttestation,
        // TODO renommer en front également
        ?RapportAuLogement $qualiteRequerant,
        ?bool $estEligible,
        public UsagerOutput $usager,
        public RequerantOutput $requerant,
        public AdresseOutput $adresse,
        // TODO implémenter
        public ?TestEligibiliteBrisPorteOutput $testEligibilite,
        public ?DeclarationFDOOutput $declarationFDO,
        public ?string $descriptionRequerant,
        public ?string $notes,
        public ?\DateTimeImmutable $dateOperation,
        public ?AdministrationType $typeAdministration,
        public bool $estPorteBlindee = false,
        /** @var PieceJointeOutput[] */
        public array $documents = [],
    ) {
        parent::__construct(
            id: $id,
            reference: $reference,
            etat: $etat,
            dateDepot: $dateDepot,
            montantIndemnisation: $montantIndemnisation,
            redacteur: $redacteur,
            typeAttestation: $typeAttestation,
            qualiteRequerant: $qualiteRequerant,
            estEligible: $estEligible,
        );
    }

    public static function creerDepuisDossier(Dossier $dossier): self
    {
        return new self(
            id: $dossier->getId(),
            reference: $dossier->getReference(),
            etat: EtatDossierOutput::depuisEtatDossier($dossier->getEtatDossier()),
            dateDepot: $dossier->getDateDepot(),
            montantIndemnisation: $dossier->getMontantIndemnisation(),
            redacteur: $dossier->getRedacteur() ? RedacteurOutput::depuisAgent($dossier->getRedacteur()) : null,
            typeAttestation: $dossier->getBrisPorte()->getTypeAttestation(),
            qualiteRequerant: $dossier->getBrisPorte()->getRapportAuLogement(),
            estEligible: $dossier->getBrisPorte()->getTestEligibilite()?->estEligible(),
            usager: UsagerOutput::depuisUsager($dossier->getUsager()),
            requerant: RequerantOutput::depuisRequerant($dossier->estPersonneMorale() ? $dossier->getRequerantPersonneMorale() : $dossier->getRequerantPersonnePhysique()),
            adresse: AdresseOutput::depuisAdresse($dossier->getBrisPorte()->getAdresse()),
            testEligibilite: TestEligibiliteBrisPorteOutput::depuisTestEligibilite($dossier->getBrisPorte()->getTestEligibilite()),
            declarationFDO: DeclarationFDOOutput::depuisDeclarationFDOBrisPorte($dossier->getBrisPorte()->getDeclarationFDO()),
            descriptionRequerant: $dossier->getBrisPorte()->getDescriptionRequerant(),
            notes: $dossier->getNotes(),
            dateOperation: $dossier->getBrisPorte()->getDateOperation(),
            typeAdministration: $dossier->getBrisPorte()->getTypeInstitutionSecuritePublique(),
            estPorteBlindee: $dossier->getBrisPorte()->estPorteBlindee(),
            documents: $dossier->getPiecesJointes()->map(fn (Document $document) => PieceJointeOutput::depuisDocument($document))->toArray(),
        );
    }
}
