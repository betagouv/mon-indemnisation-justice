<?php

namespace MonIndemnisationJustice\Api\Requerant\Dossier\Dto;

use MonIndemnisationJustice\Entity\Usager;

class UsagerDto
{
    public function __construct(
        public int $id,
        public PersonneDto $personne,
    ) {
    }

    public static function depuisUsager(Usager $usager): self
    {
        return new self(
            id: $usager->getId(),
            personne: PersonneDto::depuisPersonne($usager->getPersonne()),
        );
    }
}
