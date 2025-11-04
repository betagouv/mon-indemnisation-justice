<?php

namespace MonIndemnisationJustice\Session;

use MonIndemnisationJustice\Entity\DeclarationErreurOperationnelle;
use MonIndemnisationJustice\Entity\Requerant;
use MonIndemnisationJustice\Entity\TestEligibilite;

class PreInscription
{
    public function __construct(
        public ?TestEligibilite $testEligibilite = null,
        public ?DeclarationErreurOperationnelle $declarationErreurOperationnelle = null,
        public ?Requerant $requerant = null,
    ) {}
}
