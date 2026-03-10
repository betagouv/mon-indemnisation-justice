<?php

namespace MonIndemnisationJustice\Api\Requerant\Brouillon\Dto;

use MonIndemnisationJustice\Entity\Civilite;
use MonIndemnisationJustice\Entity\Personne;
use Symfony\Component\ObjectMapper\Attribute\Map;

#[Map(source: Personne::class)]
class PersonneDto
{
    public ?string $id;
    public ?Civilite $civilite;
    public ?string $nom;
    public ?string $nomNaissance = null;
    public ?string $prenom;
    public ?string $courriel;
    public ?string $telephone;
}
