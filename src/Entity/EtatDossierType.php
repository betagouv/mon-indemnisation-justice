<?php

namespace App\Entity;

enum EtatDossierType: string
{
    case DOSSIER_INITIE = 'DOSSIER_INITIE';
    case DOSSIER_DEPOSE = 'DOSSIER_DEPOSE';
}
