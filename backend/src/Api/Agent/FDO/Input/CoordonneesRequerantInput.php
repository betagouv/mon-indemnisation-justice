<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Input;

use MonIndemnisationJustice\Entity\Civilite;
use MonIndemnisationJustice\Entity\CoordonneesRequerant;
use Symfony\Component\ObjectMapper\Attribute\Map;

#[Map(CoordonneesRequerant::class)]
class CoordonneesRequerantInput
{
    public ?Civilite $civilite = null;
    public ?string $nom = null;
    public ?string $prenom = null;
    public ?string $telephone = null;
    public ?string $courriel = null;
}
