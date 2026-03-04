<?php

namespace MonIndemnisationJustice\Api\Requerant\Brouillon\Dto;

use MonIndemnisationJustice\Entity\GeoCodePostal;
use Symfony\Component\ObjectMapper\Attribute\Map;

#[Map(target: GeoCodePostal::class)]
class CommuneDto
{
    public ?int $id;
    public ?string $codePostal;
    #[Map(source: 'commune.nom')]
    public ?string $nom;
    #[Map(source: 'commune.departement.nom')]
    public ?string $departement;
}
