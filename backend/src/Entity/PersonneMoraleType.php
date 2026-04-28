<?php

namespace MonIndemnisationJustice\Entity;

enum PersonneMoraleType: string
{
    case ASSUREUR = 'ASSUREUR';
    case SCI = 'SCI';
    case ENTREPRISE_PRIVEE = 'ENTREPRISE_PRIVEE';
    case BAILLEUR_SOCIAL = 'BAILLEUR_SOCIAL';
    case SYNDIC = 'SYNDIC';
    case ASSOCIATION = 'ASSOCIATION';
    case COLLECTIVITE = 'COLLECTIVITE';
    case ETABLISSEMENT_PUBLIC = 'ETABLISSEMENT_PUBLIC';
}
