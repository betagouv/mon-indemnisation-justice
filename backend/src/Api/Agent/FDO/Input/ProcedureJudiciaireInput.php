<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Input;

use MonIndemnisationJustice\Entity\ProcedureJudiciaire;
use Symfony\Component\Validator\Constraints as Assert;

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

    public function versProcedureJudiciaire(): ProcedureJudiciaire
    {
        return new ProcedureJudiciaire()
            ->setNumeroProcedure($this->numeroProcedure)
            ->setServiceEnqueteur($this->serviceEnqueteur)
            ->setTelephone($this->telephone)
            ->setJuridictionOuParquet($this->juridictionOuParquet)
            ->setNomMagistrat($this->nomMagistrat);
    }
}
