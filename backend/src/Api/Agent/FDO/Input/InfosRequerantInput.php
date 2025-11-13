<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Input;

use MonIndemnisationJustice\Entity\Civilite;
use MonIndemnisationJustice\Entity\Metadonnees\InfosRequerant;
use Symfony\Component\ObjectMapper\Attribute\Map;

#[Map(InfosRequerant::class)]
class InfosRequerantInput
{
    public Civilite $civilite;
    public string $nom;
    public string $prenom;
    public string $telephone;
    public string $courriel;
    public string $message;
}
