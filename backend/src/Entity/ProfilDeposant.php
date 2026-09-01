<?php

namespace MonIndemnisationJustice\Entity;

enum ProfilDeposant: string
{
    case PARTICULIER = 'particulier';
    case REPRESENTANT = 'representant';
    case AVOCAT = 'avocat';
}
