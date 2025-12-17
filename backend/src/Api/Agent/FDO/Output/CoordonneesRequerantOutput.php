<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Output;

use MonIndemnisationJustice\Entity\Civilite;
use MonIndemnisationJustice\Entity\CoordonneesRequerant;
use Symfony\Component\ObjectMapper\Attribute\Map;

#[Map(source: CoordonneesRequerant::class)]
class CoordonneesRequerantOutput
{
    public Civilite $civilite;
    public string $nom;
    public string $prenom;
    public string $telephone;
    public string $courriel;
}
