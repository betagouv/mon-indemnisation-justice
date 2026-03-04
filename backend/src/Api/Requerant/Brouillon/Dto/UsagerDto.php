<?php

namespace MonIndemnisationJustice\Api\Requerant\Brouillon\Dto;

use MonIndemnisationJustice\Api\Requerant\Brouillon\Mapper\UsagerDtoMapper;
use MonIndemnisationJustice\Entity\Usager;
use Symfony\Component\ObjectMapper\Attribute\Map;

#[Map(source: Usager::class, transform: [UsagerDtoMapper::class])]
class UsagerDto
{
    public ?int $id;
    // Ne pas mapper puisque c'est le `UsagerDtoMapper` qui s'en charge
    #[Map(if: false)]
    public ?PersonneDto $personne = null;
}
