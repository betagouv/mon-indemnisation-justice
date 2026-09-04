<?php

namespace MonIndemnisationJustice\Entity;

enum PieceProcedure: string
{
    case ACTE_INTRODUCTIF = 'acte_introductif';
    case ECRITURES = 'ecritures';
    case ECHANGES = 'echanges';
    case DOCUMENTS_PROCEDURE = 'documents_procedure';
    case AUCUNE = 'aucune';
}
