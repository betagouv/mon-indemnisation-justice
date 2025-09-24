<?php

namespace MonIndemnisationJustice\Entity\Metadonnees;

use MonIndemnisationJustice\Entity\TypeAttestation;
use MonIndemnisationJustice\Entity\TypeInstitutionSecuritePublique;

class MetadonneesAttestation
{
    public function __construct(
        public ?TypeAttestation $typeAttestation,
        public ?TypeInstitutionSecuritePublique $typeInstitutionSecuritePublique
    ) {}
}
