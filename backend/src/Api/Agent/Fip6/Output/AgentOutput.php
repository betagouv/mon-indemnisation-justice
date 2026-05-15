<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Output;

use MonIndemnisationJustice\Entity\AdministrationType;
use MonIndemnisationJustice\Entity\Agent;

class AgentOutput
{
    public function __construct(
        public readonly int $id,
        public readonly string $nom,
        public readonly string $prenom,
        public readonly string $courriel,
        public readonly string $identifiant,
        public readonly AdministrationType $administration,
        public readonly array $roles,
        public readonly ?\DateTimeImmutable $dateCreation = null,
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
            administration: $agent->getAdministration()->getType(),
            roles: $agent->getRoles(),
            dateCreation: $agent->getDateCreation(),
        );
    }
}
