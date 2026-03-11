<?php

namespace MonIndemnisationJustice\Api\Requerant\Dossier\Dto;

use MonIndemnisationJustice\Entity\GeoCodePostal;

class CommuneDto
{
    public function __construct(
        public ?int    $id,
        public ?string $codePostal,
        public ?string $nom,
        public ?string $departement,
    )
    {

    }

    public function versCommune(?GeoCodePostal $commune): ?GeoCodePostal
    {
        return ($commune ?? new GeoCodePostal())
            ->setCodePostal($this->codePostal);
    }

    public static function depuisCommune(?GeoCodePostal $geoCodePostal): ?self
    {
        if (null === $geoCodePostal) {
            return null;
        }

        return new self(
            id: $geoCodePostal->getId(),
            codePostal: $geoCodePostal->getCodePostal(),
            nom: $geoCodePostal->getCommune()->getNom(),
            departement: $geoCodePostal->getCommune()->getDepartement()->getNom(),
        );
    }
}
