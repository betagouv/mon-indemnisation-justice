<?php

namespace MonIndemnisationJustice\Api\Requerant\Brouillon\Dto;

use MonIndemnisationJustice\Entity\Usager;
use Symfony\Component\ObjectMapper\Attribute\Map;

#[Map(target: Usager::class)]
class UsagerDto
{
    public ?int $id;
    public ?PersonneDto $personne;
}
