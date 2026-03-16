<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Output;

use MonIndemnisationJustice\Entity\ProcedureJudiciaire;

class ProcedureJudiciaireOutput
{
    public function __construct(
        public readonly string $numeroProcedure,
        public readonly string $serviceEnqueteur,
        public readonly string $juridictionOuParquet,
        public readonly string $nomMagistrat,
        public readonly ?string $telephone,
    ) {
    }

    public static function depuisProcedureJudiciaire(?ProcedureJudiciaire $procedureJudiciaire): ?self
    {
        if (null === $procedureJudiciaire) {
            return null;
        }

        return new self(
            numeroProcedure: $procedureJudiciaire->getNumeroProcedure(),
            serviceEnqueteur: $procedureJudiciaire->getServiceEnqueteur(),
            juridictionOuParquet: $procedureJudiciaire->getJuridictionOuParquet(),
            nomMagistrat: $procedureJudiciaire->getNomMagistrat(),
            telephone: $procedureJudiciaire->getTelephone(),
        );
    }
}
