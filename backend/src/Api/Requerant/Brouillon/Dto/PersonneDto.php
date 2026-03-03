<?php

namespace MonIndemnisationJustice\Api\Requerant\Brouillon\Dto;

use MonIndemnisationJustice\Entity\Civilite;

class PersonneDto
{
    public ?string $id;
    public ?Civilite $civilite;
    public ?string $nom;
    public ?string $nomNaissance;
    public ?string $prenom;
    public ?string $courriel;
    public ?string $telephone;
}
