<?php

namespace MonIndemnisationJustice\Api\Requerant\Dossier\Dto;

use MonIndemnisationJustice\Entity\EtatDossier;

class EtatDossierDto
{
    public function __construct(
        public EtatDossierUsager  $etat,
        public \DateTimeImmutable $date,
        // Une référence vers l'agent
        public ?array             $agent = null,
        // Une référence vers l'agent
        public ?array             $requerant = null
    )
    {
    }

    public static function depuisEtatDossier(EtatDossier $etatDossier): self
    {
        return new self(
            etat: EtatDossierUsager::depuisEtatDossier($etatDossier->getEtat()),
            date: $etatDossier->getDateEntree(),
            agent: $etatDossier->getAgent() ? [
                'id' => $etatDossier->getAgent()->getId(),
                'nom' => $etatDossier->getAgent()->getNomComplet(capital: true),
            ] : null,
            requerant: $etatDossier->getRequerant() ? [
                'id' => $etatDossier->getRequerant()->getId(),
                'nom' => $etatDossier->getRequerant()->getNomCourant(capital: true),
            ] : null,
        );
    }
}
