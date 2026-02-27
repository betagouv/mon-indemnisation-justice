<?php

namespace MonIndemnisationJustice\Api\Requerant\Brouillon\Dto;

use MonIndemnisationJustice\Entity\Requerant;
use Symfony\Component\ObjectMapper\Attribute\Map;

#[Map(target: Requerant::class)]
class UsagerDto
{
    public ?int $id;
    public ?PersonneDto $personne;
}
