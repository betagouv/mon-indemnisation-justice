<?php

namespace MonIndemnisationJustice\Api\Requerant\Request\Attribute;

use MonIndemnisationJustice\Api\Requerant\Dossier\Dto\DossierDto;
use MonIndemnisationJustice\Entity\Dossier;

readonly class DossierArgument
{
    public function __construct(
        public Dossier $dossier,
        public DossierDto $dto,
    ) {
    }
}
