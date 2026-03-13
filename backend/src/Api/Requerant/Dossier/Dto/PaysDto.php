<?php

namespace MonIndemnisationJustice\Api\Requerant\Dossier\Dto;

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
        return ($pays ?? new GeoPays())
            ->setCode($this->code)
            ->setNom($this->nom);
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
