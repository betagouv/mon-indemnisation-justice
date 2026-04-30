<?php

namespace MonIndemnisationJustice\Api\Requerant\Dossier\Dto;

use MonIndemnisationJustice\Api\Requerant\Dossier\Normalization\EntityResolveur;
use MonIndemnisationJustice\Entity\GeoPays;

class PaysDto
{
    public function __construct(
        public ?string $code = null,
        public ?string $nom = null,
    ) {
    }

    public function versPays(?GeoPays $pays): GeoPays
    {
        return $this->code !== $pays?->getCode() ? EntityResolveur::resoudre(GeoPays::class, $this->code) : $pays;
    }

    public static function depuisPays(?GeoPays $pays): ?self
    {
        if (null === $pays) {
            return null;
        }

        return new self(
            code: $pays->getCode(),
            nom: $pays->getNom(),
        );
    }
}
