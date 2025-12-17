<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Transformers;

use MonIndemnisationJustice\Api\Agent\FDO\Input\DeclarationFDOBrisPorteInput;
use MonIndemnisationJustice\Api\Agent\FDO\Output\AdresseOutput;
use MonIndemnisationJustice\Api\Agent\FDO\Output\AgentOutput;
use MonIndemnisationJustice\Api\Agent\FDO\Output\CoordonneesRequerantOutput;
use MonIndemnisationJustice\Api\Agent\FDO\Output\DeclarationFDOBrisPorteOutput;
use MonIndemnisationJustice\Api\Agent\FDO\Output\ProcedureJudiciaireOutput;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

class DeclarationFDOBrisPorteOutputMapper
{
    public static function mapper(DeclarationFDOBrisPorte|DeclarationFDOBrisPorteInput $declaration, ObjectMapperInterface $mapper): DeclarationFDOBrisPorteOutput
    {
        $output = $mapper->map($declaration, DeclarationFDOBrisPorteOutput::class);

        // Le mapping récursif ne fonctionnant pas, on doit "sous-mapper“ ici
        $output->agent = $mapper->map($declaration->getAgent(), AgentOutput::class);
        $output->adresse = $mapper->map($declaration->getAdresse(), AdresseOutput::class);
        $output->procedure = $mapper->map($declaration->getProcedure(), ProcedureJudiciaireOutput::class);

        $output->infosRequerant = null !== $declaration->getInfosRequerant() ? $mapper->map($declaration->getInfosRequerant(), CoordonneesRequerantOutput::class) : null;

        return $output;
    }
}
