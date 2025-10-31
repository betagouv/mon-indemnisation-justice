<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Output;

use MonIndemnisationJustice\Entity\Adresse;
use Symfony\Component\ObjectMapper\Attribute\Map;

#[Map(source: Adresse::class)]
class AdresseOutput
{
    public string $ligne1;
    public string $ligne2;
    public string $codePostal;
    public string $localite;
}
