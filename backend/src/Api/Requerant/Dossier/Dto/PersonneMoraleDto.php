<?php

namespace MonIndemnisationJustice\Api\Requerant\Dossier\Dto;

use MonIndemnisationJustice\Entity\PersonneMorale;
use MonIndemnisationJustice\Entity\PersonneMoraleType;

class PersonneMoraleDto
{
    public function __construct(
        public ?string $raisonSociale,
        public ?PersonneMoraleType $typePersonneMorale,
        public ?string $siren,
        public ?AdresseDto $adresse,
        public ?PersonneDto $representantLegal,
    ) {

    }

    public function versPersonneMorale(?PersonneMorale $personneMorale): PersonneMorale
    {
        if (null === $personneMorale
        ) {
            $personneMorale = new PersonneMorale();
        }

        if ($this->typePersonneMorale) {
            $personneMorale->setType($this->typePersonneMorale);
        }

        return $personneMorale
            ->setRaisonSociale($this->raisonSociale)
            ->setSirenSiret($this->siren)
            ->setRepresentantLegal($this->representantLegal?->versPersonne($personneMorale?->getRepresentantLegal()))
            ->setAdresse($this->adresse?->versAdresse($personneMorale?->getAdresse()));
    }

    public static function depuisPersonneMorale(?PersonneMorale $personneMorale): ?self
    {
        if (null === $personneMorale) {
            return null;
        }

        return new self(
            raisonSociale: $personneMorale->getRaisonSociale(),
            typePersonneMorale: $personneMorale->getType(),
            siren: $personneMorale->getSirenSiret(),
            adresse: AdresseDto::depuisAdresse($personneMorale->getAdresse()),
            representantLegal: PersonneDto::depuisPersonne($personneMorale->getRepresentantLegal()),
        );
    }
}
