<?php

namespace MonIndemnisationJustice\Entity\Metadonnees;

use MonIndemnisationJustice\Entity\AdministrationType;
use MonIndemnisationJustice\Entity\TypeAttestation;

class MetadonneesAttestation
{
    public function __construct(
        public ?TypeAttestation $typeAttestation,
        public ?AdministrationType $typeAdministration,
    ) {
    }
}
