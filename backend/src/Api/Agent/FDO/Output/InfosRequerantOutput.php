<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Output;

use MonIndemnisationJustice\Entity\Metadonnees\InfosRequerant;
use Symfony\Component\ObjectMapper\Attribute\Map;

#[Map(source: InfosRequerant::class)]
class InfosRequerantOutput
{
    public string $nom;
    public string $prenom;
    public string $telephone;
    public string $courriel;
    public string $message;
}
