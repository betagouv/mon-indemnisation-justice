<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Output;

use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\RapportAuLogement;
use MonIndemnisationJustice\Entity\TypeAttestation;
use Symfony\Component\Serializer\Attribute\Ignore;

readonly class DossierApercuOutput extends BaseDossierOutput
{
    #[Ignore]
    public ?string $montantIndemnisation;
    #[Ignore]
    public ?RapportAuLogement $qualiteRequerant;

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
        public bool $issuDeclarationFDO,
        public string $requerant,
        public string $adresse,
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
            issuDeclarationFDO: null !== $dossier->getBrisPorte()->getDeclarationFDO(),
            requerant: $dossier->estPersonneMorale() ?
                $dossier->getRequerantPersonneMorale()->getRaisonSociale() :
                $dossier->getRequerantPersonnePhysique()?->getPersonne()->getNomCourant(capital: true),
            adresse: $dossier->getBrisPorte()->getAdresse()->getLibelle(),
        );
    }
}
