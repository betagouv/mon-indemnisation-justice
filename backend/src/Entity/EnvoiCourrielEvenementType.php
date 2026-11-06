<?php

namespace MonIndemnisationJustice\Entity;

enum EnvoiCourrielEvenementType: string
{
    case EXPEDIE = 'EXPEDIE';
    case RECU = 'RECU';
    case OUVERT = 'OUVERT';
    case CLIQUE = 'CLIQUE';
    case REJETE = 'REJETE';
}
