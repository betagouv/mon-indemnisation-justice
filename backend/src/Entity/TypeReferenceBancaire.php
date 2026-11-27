<?php

namespace MonIndemnisationJustice\Entity;

enum TypeReferenceBancaire: string
{
    /** Relevé d'identité bancaire français : code banque, code guichet, numéro de compte et clé RIB. */
    case RIB = 'RIB';
    /** IBAN au format `FRkk BBBBB GGGGG CCCCCCCCCCC KK` */
    case IBAN = 'IBAN';
}
