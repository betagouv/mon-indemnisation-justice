<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Output;

use MonIndemnisationJustice\Entity\Agent;

readonly class AgentOutput
{
    public function __construct(
        int $id,
        string $prenom,
        string $nom,
        string $courriel,
        string $administration,
        ?string $telephone = null,
        array $roles = [],
    ) {

    }

    public static function depuisAgent(Agent $agent): self
    {
        return new self(
            id: $agent->getId(),
            prenom: $agent->getPrenom(),
            nom: $agent->getNom(),
            courriel: $agent->getEmail(),
            administration: $agent->getAdministration()->getType()->value,
            telephone: $agent->getTelephone(),
            roles: $agent->getRoles()
        );
    }
}
