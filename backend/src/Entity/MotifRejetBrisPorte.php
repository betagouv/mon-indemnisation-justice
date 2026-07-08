<?php

namespace MonIndemnisationJustice\Entity;

enum MotifRejetBrisPorte: string
{
    // Le requérant est le mis en cause
    case MIS_EN_CAUSE = 'MIS_EN_CAUSE';
    // Le requérant est mis en cause par l'intervention d'un chien renifleur
    case MIS_EN_CAUSE_CHIEN = 'MIS_EN_CAUSE_CHIEN';
    // Le locataire est le mis en cause
    case LOCATAIRE = 'LOCATAIRE';
    // Le locataire est mis en cause par l'intervention d'un chien renifleur
    case LOCATAIRE_CHIEN = 'LOCATAIRE_CHIEN';
    // Le locataire via une plateforme type Airbnb est mis en cause
    case LOCATAIRE_AIRBNB = 'LOCATAIRE_AIRBNB';
    // Le locataire hébergait le mis en cause
    case LOCATAIRE_HEBERGEANT = 'LOCATAIRE_HEBERGEANT';
    // Le locataire est le mis en cause et l'intervention a entrainé des dégradations dans les parties communes
    case LOCATAIRE_PARTIES_COMMUNES = 'LOCATAIRE_PARTIES_COMMUNES';
}
