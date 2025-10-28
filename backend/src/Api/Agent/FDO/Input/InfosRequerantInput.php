<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Input;

use MonIndemnisationJustice\Entity\Metadonnees\InfosRequerant;
use Symfony\Component\ObjectMapper\Attribute\Map;

#[Map(InfosRequerant::class)]
class InfosRequerantInput
{
    public string $nom;
    public string $prenom;
    public string $telephone;
    public string $courriel;
    public string $message;
}
