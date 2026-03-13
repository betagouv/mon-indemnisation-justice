<?php

namespace MonIndemnisationJustice\Api\Requerant\Request\Attribute;

use MonIndemnisationJustice\Entity\DossierType;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class MapDossierDto
{
    public function __construct(
        public string $reference = 'reference',
        public DossierType $type = DossierType::BRIS_PORTE,
    ) {
    }
}
