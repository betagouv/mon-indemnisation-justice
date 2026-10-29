<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Dto;

use MonIndemnisationJustice\Entity\Agent;

readonly class AgentDto
{
    public function __construct(
        public int $id,
        public string $prenom,
        public string $nom,
        public string $courriel,
        public string $administration,
        public ?string $telephone = null,
        public array $roles = [],
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
