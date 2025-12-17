<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Input;

use MonIndemnisationJustice\Entity\ProcedureJudiciaire;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Validator\Constraints as Assert;

#[Map(target: ProcedureJudiciaire::class)]
class ProcedureJudiciaireInput
{
    #[Assert\NotNull(message: 'Le numéro de procédure est requis')]
    public ?string $numeroProcedure = null;
    #[Assert\NotNull(message: 'Le nom du service enquêteur est requis')]
    public ?string $serviceEnqueteur = null;

    #[Assert\NotNull(message: 'La numéro de téléphone du service enquêteur est requis')]
    public ?string $telephone = null;
    public ?string $juridictionOuParquet = null;
    public ?string $nomMagistrat = null;
}
