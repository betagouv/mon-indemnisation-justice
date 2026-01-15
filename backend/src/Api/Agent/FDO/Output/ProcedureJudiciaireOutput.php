<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Output;

use MonIndemnisationJustice\Entity\ProcedureJudiciaire;
use Symfony\Component\ObjectMapper\Attribute\Map;

#[Map(source: ProcedureJudiciaire::class)]
class ProcedureJudiciaireOutput
{
    public string $numeroProcedure;
    public string $serviceEnqueteur;

    public string $telephone;
    public ?string $juridictionOuParquet = null;
    public ?string $nomMagistrat = null;
}
