<?php

namespace MonIndemnisationJustice\Api\Requerant\Dossier\Dto;

use MonIndemnisationJustice\Api\Requerant\Dossier\Normalization\EntityResolveur;
use MonIndemnisationJustice\Entity\GeoCodePostal;

class CommuneDto
{
    public function __construct(
        public ?int $id,
        public ?string $codePostal,
        public ?string $nom,
        public ?string $departement,
    ) {

    }

    public function versCommune(?GeoCodePostal $commune): ?GeoCodePostal
    {
        return $this->id !== $commune?->getId() ? EntityResolveur::resoudre(GeoCodePostal::class, $this->id) : $commune;
    }

    public static function depuisCommune(?GeoCodePostal $geoCodePostal): ?self
    {
        if (null === $geoCodePostal) {
            return null;
        }

        return new self(
            id: $geoCodePostal->getId(),
            codePostal: $geoCodePostal->getCodePostal(),
            nom: null, // $geoCodePostal->getCommune()->getNom(),
            departement: null, // $geoCodePostal->getCommune()->getDepartement()->getNom(),
        );
    }
}
