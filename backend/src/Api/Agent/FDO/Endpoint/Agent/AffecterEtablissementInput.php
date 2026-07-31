<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Endpoint\Agent;

readonly class AffecterEtablissementInput
{
    public function __construct(
        public bool $estExempt = false,
        public ?string $etablissement = null,
        public ?\DateTime $dateAffectation = null,
    ) {
    }
}
