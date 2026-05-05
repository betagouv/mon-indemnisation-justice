<?php

namespace MonIndemnisationJustice\Api\Requerant\Dossier\Dto;

use MonIndemnisationJustice\Entity\Usager;

class UsagerDto
{
    public function __construct(
        public int $id,
        public string $courriel,
        public string $nom,
        public PersonneDto $personne,
        public ?string $telephone = null,
    ) {
    }

    public static function depuisUsager(Usager $usager): self
    {
        return new self(
            id: $usager->getId(),
            courriel: $usager->getEmail(),
            nom: $usager->getNomCourant(capital: true),
            personne: PersonneDto::depuisPersonne($usager->getPersonne()),
            telephone: $usager->getPersonne()->getTelephone(),
        );
    }
}
