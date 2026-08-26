<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Output;

use MonIndemnisationJustice\Entity\ProcedureJudiciaire;

class ProcedureJudiciaireOutput
{
    public function __construct(
        public readonly string $numeroProcedure,
        public readonly string $serviceEnqueteur,
        public readonly string $telephone,
        public readonly ?string $juridictionOuParquet = null,
        public readonly ?string $nomMagistrat = null,
    ) {
    }

    public static function depuisProcedureJudiciaire(ProcedureJudiciaire $procedureJudiciaire): self
    {
        return new self(
            numeroProcedure: $procedureJudiciaire->getNumeroProcedure(),
            serviceEnqueteur: $procedureJudiciaire->getServiceEnqueteur(),
            telephone: $procedureJudiciaire->getTelephone(),
            juridictionOuParquet: $procedureJudiciaire->getJuridictionOuParquet(),
            nomMagistrat: $procedureJudiciaire->getNomMagistrat(),
        );
    }
}
