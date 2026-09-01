<?php

namespace MonIndemnisationJustice\Api\Requerant\Dossier\Dto;

use MonIndemnisationJustice\Entity\DemandeDysfonctionnement;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\ProfilDeposant;

class DemandeDysfonctionnementDto
{
    public function __construct(
        public int $id,
        public ?string $reference,
        public int $usager,
        public ?EtatDossierDto $etatActuel = null,
        public ?ProfilDeposant $profil = null,
        public ?string $identiteNom = null,
        public ?string $identitePrenom = null,
        public ?string $identiteCourriel = null,
        public ?string $identiteCabinet = null,
        public ?string $identiteBarreau = null,
        public bool $pieceIdentiteDeposee = false,
        public bool $justificatifPouvoirDepose = false,
        public ?string $compteNom = null,
        public ?string $comptePrenom = null,
        public ?string $compteCourriel = null,
        public ?string $compteCabinet = null,
        public ?string $compteBarreau = null,
    ) {
    }

    public function versDossier(Dossier $dossier): Dossier
    {
        return $dossier->setDemandeDysfonctionnement(
            ($dossier->getDemandeDysfonctionnement() ?? new DemandeDysfonctionnement())
                ->setProfil($this->profil)
                ->setIdentiteNom($this->identiteNom)
                ->setIdentitePrenom($this->identitePrenom)
                ->setIdentiteCourriel($this->identiteCourriel)
                ->setIdentiteCabinet($this->identiteCabinet)
                ->setIdentiteBarreau($this->identiteBarreau)
        );
    }

    public static function depuisDossier(Dossier $dossier): self
    {
        $demande = $dossier->getDemandeDysfonctionnement();
        $usager = $dossier->getUsager();
        $avocat = $usager->getAvocat();

        return new self(
            id: $dossier->getId(),
            reference: $dossier->getReference(),
            usager: $usager->getId(),
            etatActuel: EtatDossierDto::depuisEtatDossier($dossier->getEtatDossier()),
            profil: $demande?->getProfil(),
            identiteNom: $demande?->getIdentiteNom(),
            identitePrenom: $demande?->getIdentitePrenom(),
            identiteCourriel: $demande?->getIdentiteCourriel(),
            identiteCabinet: $demande?->getIdentiteCabinet(),
            identiteBarreau: $demande?->getIdentiteBarreau(),
            pieceIdentiteDeposee: null !== $dossier->getDocumentParType(DocumentType::TYPE_CARTE_IDENTITE),
            justificatifPouvoirDepose: null !== $dossier->getDocumentParType(DocumentType::TYPE_POUVOIR_SIGNATAIRE),
            compteNom: $usager->getPersonne()->getNom(),
            comptePrenom: $usager->getPersonne()->getPrenom(),
            compteCourriel: $usager->getPersonne()->getCourriel(),
            compteCabinet: $avocat?->getCabinet(),
            compteBarreau: $avocat?->getBarreau()->getNom(),
        );
    }
}
