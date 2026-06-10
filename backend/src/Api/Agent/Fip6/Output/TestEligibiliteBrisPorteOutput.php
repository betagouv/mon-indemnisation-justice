<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Output;

use MonIndemnisationJustice\Entity\RapportAuLogement;
use MonIndemnisationJustice\Entity\TestEligibiliteBrisPorte;

class TestEligibiliteBrisPorteOutput
{
    public function __construct(
        public ?RapportAuLogement $rapportAuLogement,
        public bool $estVise,
        public ?bool $estHebergeant = null,
        public ?bool $aContacteAssurance = null,
        public ?bool $aContacteBailleur = null,
    ) {
    }

    public static function depuisTestEligibilite(?TestEligibiliteBrisPorte $testEligibilite = null): ?self
    {
        if (null === $testEligibilite) {
            return null;
        }

        return new self(
            rapportAuLogement: $testEligibilite->rapportAuLogement,
            estVise: $testEligibilite->estVise,
            estHebergeant: $testEligibilite->estHebergeant,
            aContacteAssurance: $testEligibilite->aContacteAssurance,
            aContacteBailleur: $testEligibilite->aContacteBailleur,
        );
    }
}
