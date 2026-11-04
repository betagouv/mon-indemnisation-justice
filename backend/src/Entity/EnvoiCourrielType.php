<?php

namespace MonIndemnisationJustice\Entity;

enum EnvoiCourrielType: string
{
    // Invitation à déclarer à une demande d'indemnisation pour un bris de porte à partir de la déclaraten tout juste envoyée
    // par l'agent des FDO
    case INVITATION_FDO = 'INVITATION_FDO';
}
