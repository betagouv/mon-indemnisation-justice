<?php

namespace MonIndemnisationJustice\Service\Signature\Evenement;

enum EvenementSignatureType: string
{
    case EXPIREE = 'EXPIREE';
    case ECHOUEE = 'ECHOUEE';
    case SIGNEE = 'SIGNEE';
}
