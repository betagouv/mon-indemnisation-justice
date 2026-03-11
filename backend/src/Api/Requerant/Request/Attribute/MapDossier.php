<?php

namespace MonIndemnisationJustice\Api\Requerant\Request\Attribute;

use MonIndemnisationJustice\Entity\DossierType;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class MapDossier
{
    public function __construct(
        public string $reference,
        public DossierType $typeBRIS_PORTE,
    ) {
    }
}
