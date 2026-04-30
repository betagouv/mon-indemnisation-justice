<?php

namespace MonIndemnisationJustice\Api\Requerant\Dossier\Dto;

use MonIndemnisationJustice\Entity\Adresse;

class AdresseDto
{
    public function __construct(
        public string $ligne1 = '',
        public ?string $ligne2 = null,
        public string $codePostal = '',
        public string $commune = '',
    ) {

    }

    public function versAdresse(?Adresse $adresse = null): Adresse
    {
        return ($adresse ?? new Adresse())
            ->setLigne1($this->ligne1)
            ->setLigne2($this->ligne2)
            ->setCodePostal($this->codePostal)
            ->setLocalite($this->commune);
    }

    public static function depuisAdresse(?Adresse $adresse): ?self
    {
        if (null === $adresse) {
            return null;
        }

        return new self(
            ligne1: $adresse->getLigne1() ?? '',
            ligne2: $adresse->getLigne2(),
            codePostal: $adresse->getCodePostal() ?? '',
            commune: $adresse->getLocalite() ?? '',
        );
    }
}
