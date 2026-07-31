<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Output;

use MonIndemnisationJustice\Entity\AdministrationType;
use MonIndemnisationJustice\Entity\AffectationAgentFDO;
use MonIndemnisationJustice\Entity\Agent;

class MoiOutput
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
        /** @var array<AffectationAgentFDOOutput>|false $affectations */
        public readonly array|false $affectations = [],
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
            affectations: $agent->estExempteAffectation() ?
                false :
                $agent->getAffectations()
                    ->map(fn (AffectationAgentFDO $affectation) => AffectationAgentFDOOutput::depuisAffectation($affectation))->toArray(),
        );
    }
}
