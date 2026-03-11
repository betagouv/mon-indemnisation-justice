<?php

namespace MonIndemnisationJustice\Api\Requerant\Dossier\Dto;

use MonIndemnisationJustice\Entity\PersonneMorale;

class PersonneMoraleDto
{
    public function __construct(
        public ?string      $raisonSociale,
        public ?string      $siren,
        public ?PersonneDto $representantLegal,
    )
    {

    }

    public function versPersonneMorale(?PersonneMorale $personneMorale): PersonneMorale
    {
        return ($personneMorale ?? new PersonneMorale())
            ->setRaisonSociale($this->raisonSociale)
            ->setSirenSiret($this->siren)
            ->setRepresentantLegal($this->representantLegal?->versPersonne($personneMorale?->getRepresentantLegal()));
    }

    public static function depuisPersonneMorale(?PersonneMorale $personneMorale): ?self
    {
        if (null === $personneMorale) {
            return null;
        }

        return new self(
            raisonSociale: $personneMorale->getRaisonSociale(),
            siren: $personneMorale->getSirenSiret(),
            representantLegal: PersonneDto::depuisPersonne($personneMorale->getRepresentantLegal()),
        );
    }
}
