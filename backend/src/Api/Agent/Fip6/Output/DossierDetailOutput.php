<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Output;

use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\RapportAuLogement;
use MonIndemnisationJustice\Entity\TypeAttestation;
use MonIndemnisationJustice\Entity\TypeInstitutionSecuritePublique;

readonly class DossierDetailOutput extends BaseDossierOutput
{
    public function __construct(
        int $id,
        ?string $reference,
        EtatDossierOutput $etat,
        ?\DateTime $dateDepot,
        ?string $montantIndemnisation,
        ?int $redacteur,
        ?TypeAttestation $typeAttestation,
        // TODO renommer en front également
        ?RapportAuLogement $qualiteRequerant,
        ?bool $estEligible,
        public RequerantOutput $requerant,
        public AdresseOutput $adresse,
        // TODO implémenter
        public ?TestEligibiliteOutput $testEligibilite,
        public ?DeclarationFDOOutput $declarationFDO,
        public ?string $descriptionRequerant,
        public ?string $notes,
        public ?\DateTime $dateOperation,
        public ?TypeInstitutionSecuritePublique $typeInstitutionSecuritePublique,
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
            redacteur: $dossier->getRedacteur()?->getId(),
            typeAttestation: $dossier->getBrisPorte()->getTypeAttestation(),
            qualiteRequerant: $dossier->getBrisPorte()->getRapportAuLogement(),
            estEligible: $dossier->getBrisPorte()->getTestEligibilite()?->estEligible(),
            requerant: RequerantOutput::depuisRequerant($dossier->estPersonneMorale() ? $dossier->getRequerantPersonneMorale() : $dossier->getRequerantPersonnePhysique()),
            adresse: AdresseOutput::depuisAdresse($dossier->getBrisPorte()->getAdresse()),
            testEligibilite: TestEligibiliteOutput::depuisTestEligibilite($dossier->getBrisPorte()->getTestEligibilite()),
            declarationFDO: DeclarationFDOOutput::depuisDeclarationFDOBrisPorte($dossier->getBrisPorte()->getDeclarationFDO()),
            descriptionRequerant: $dossier->getBrisPorte()->getDescriptionRequerant(),
            notes: $dossier->getNotes(),
            dateOperation: $dossier->getBrisPorte()->getDateOperation(),
            typeInstitutionSecuritePublique: $dossier->getBrisPorte()->getTypeInstitutionSecuritePublique(),
            estPorteBlindee: $dossier->getBrisPorte()->estPorteBlindee(),
            documents: $dossier->getDocuments()->map(fn (Document $document) => PieceJointeOutput::depuisDocument($document))->toArray(),
        );
    }
}
