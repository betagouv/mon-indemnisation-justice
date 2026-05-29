<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Output;

use MonIndemnisationJustice\Entity\Agent;

class RedacteurOutput
{
    public function __construct(
        public readonly int $id,
        public readonly string $nom,
    ) {
    }

    public static function depuisAgent(Agent $agent): self
    {
        return new self(
            id: $agent->getId(),
            nom: $agent->getNomComplet(capital: true),
        );
    }
}
