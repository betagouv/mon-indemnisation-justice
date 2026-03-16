<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Output;

use MonIndemnisationJustice\Entity\Administration;
use MonIndemnisationJustice\Entity\Agent;
use Symfony\Component\ObjectMapper\Attribute\Map;

#[Map(source: Agent::class)]
class AgentOutput
{
    public function __construct(
        public readonly int $id,
        public readonly string $nom,
        public readonly string $prenom,
        // #[ readonlyMap(source: 'email')]
        public readonly string $courriel,
        public readonly string $identifiant,
        // #[ readonlyMap(source: 'administration.value')]
        public readonly Administration $administration,
        public readonly array $roles,
        // #[ readonlyMap(source: 'dateCreation')]
        public readonly ?\DateTimeInterface $dateCreation = null,
    ) {

    }

    public static function depuisAgent(?Agent $agent): ?self
    {
        if (null === $agent) {
            return null;
        }

        return new self(
            id: $agent->getId(),
            nom: $agent->getNom(),
            prenom: $agent->getPrenom(),
            courriel: $agent->getEmail(),
            identifiant: $agent->getIdentifiant(),
            administration: $agent->getAdministration(),
            roles: $agent->getRoles(),
            dateCreation: $agent->getDateCreation(),
        );
    }
}
