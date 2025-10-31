<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Output;

use MonIndemnisationJustice\Entity\ProcedureJudiciaire;
use Symfony\Component\ObjectMapper\Attribute\Map;

#[Map(source: ProcedureJudiciaire::class)]
class ProcedureOutput
{
    public string $numeroProcedure;
    public string $serviceEnqueteur;
    public string $juridictionOuParquet;
    public string $nomMagistrat;
}
