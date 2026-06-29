<?php

namespace MonIndemnisationJustice\Entity;

enum TypeDecision: string
{
    case JUGEMENT_PREMIERE_INSTANCE = 'jugement_premiere_instance';
    case ARRET_COUR_APPEL = 'arret_cour_appel';
    case ARRET_COUR_CASSATION = 'arret_cour_cassation';
    case AUCUNE = 'aucune';
}
