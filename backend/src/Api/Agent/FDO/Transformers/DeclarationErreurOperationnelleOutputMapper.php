<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Transformers;

use MonIndemnisationJustice\Api\Agent\FDO\Output\AdresseOutput;
use MonIndemnisationJustice\Api\Agent\FDO\Output\DeclarationErreurOperationnelleOutput;
use MonIndemnisationJustice\Api\Agent\FDO\Output\InfosRequerantOutput;
use MonIndemnisationJustice\Api\Agent\FDO\Output\ProcedureOutput;
use MonIndemnisationJustice\Entity\DeclarationErreurOperationnelle;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

class DeclarationErreurOperationnelleOutputMapper
{
    public static function mapper(DeclarationErreurOperationnelle $declaration, ObjectMapperInterface $mapper): DeclarationErreurOperationnelleOutput
    {
        $output = $mapper->map($declaration, DeclarationErreurOperationnelleOutput::class);

        // Le mapping récursif ne fonctionnant pas, on doit "sous-mapper“ ici
        $output->adresse = $mapper->map($declaration->getAdresse(), AdresseOutput::class);
        $output->procedure = $mapper->map($declaration->getProcedure(), ProcedureOutput::class);
        $output->infosRequerant = $mapper->map($declaration->getInfosRequerant(), InfosRequerantOutput::class);

        return $output;
    }
}
