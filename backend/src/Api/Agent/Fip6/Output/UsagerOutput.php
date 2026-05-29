<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Output;

use MonIndemnisationJustice\Entity\Civilite;
use MonIndemnisationJustice\Entity\Usager;

class UsagerOutput
{
    public function __construct(
        public int $id,
        public Civilite $civilite,
        public string $nom,
        public string $prenom,
    ) {
    }

    public static function depuisUsager(Usager $usager): self
    {
        return new self(
            id: $usager->getId(),
            civilite: $usager->getPersonne()->getCivilite(),
            nom: $usager->getPersonne()->getNom(),
            prenom: $usager->getPersonne()->getPrenom(),
        );
    }
}
