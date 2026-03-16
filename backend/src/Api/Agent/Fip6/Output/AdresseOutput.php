<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Output;

use MonIndemnisationJustice\Entity\Adresse;

class AdresseOutput
{
    public function __construct(
        public readonly string $ligne1,
        public readonly ?string $ligne2,
        public readonly string $codePostal,
        public readonly string $localite,
    ) {

    }

    public static function depuisAdresse(Adresse $adresse): self
    {
        return new self(
            $adresse->getLigne1(),
            $adresse->getLigne2(),
            $adresse->getCodePostal(),
            $adresse->getLocalite(),
        );
    }
}
