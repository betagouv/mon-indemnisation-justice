<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Input;

use MonIndemnisationJustice\Entity\Adresse;
use Symfony\Component\ObjectMapper\Attribute\Map;

#[Map(target: Adresse::class)]
class AdresseInput
{
    public string $ligne1;
    public string $ligne2;
    public string $codePostal;
    public string $localite;
}
