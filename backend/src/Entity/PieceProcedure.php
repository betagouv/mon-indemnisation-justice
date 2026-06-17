<?php

namespace MonIndemnisationJustice\Entity;

enum PieceProcedure: string
{
    case ASSIGNATION = 'assignation';
    case DECISIONS_JUGE = 'decisions_juge';
    case CALENDRIER = 'calendrier';
    case ECRITURES = 'ecritures';
    case CONVOCATIONS = 'convocations';
    case RENVOI = 'renvoi';
    case ECHANGES = 'echanges';
    case APPEL = 'appel';
}
