<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Input;

use MonIndemnisationJustice\Entity\ProcedureJudiciaire;
use Symfony\Component\ObjectMapper\Attribute\Map;

#[Map(target: ProcedureJudiciaire::class)]
class ProcedureInput
{
    public string $numeroProcedure;
    public string $serviceEnqueteur;
    public string $juridictionOuParquet;
    public string $nomMagistrat;
    public string $commentaire;
}
