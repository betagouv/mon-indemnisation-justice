<?php

namespace MonIndemnisationJustice\Entity;

enum RapportAuLogement: string
{
    case PROPRIETAIRE = 'PROPRIETAIRE';
    case LOCATAIRE = 'LOCATAIRE';
    case BAILLEUR = 'BAILLEUR';
    case BAILLEUR_SOCIAL = 'BAILLEUR_SOCIAL';
    case AUTRE = 'AUTRE';

    public function getLibelle(): string
    {
        return match ($this) {
            RapportAuLogement::PROPRIETAIRE => 'Propriétaire occupant',
            RapportAuLogement::LOCATAIRE => 'Locataire',
            RapportAuLogement::BAILLEUR => 'Propriétaire bailleur',
            RapportAuLogement::BAILLEUR_SOCIAL => 'Bailleur social',
            RapportAuLogement::AUTRE => 'Autre',
        };
    }
}
