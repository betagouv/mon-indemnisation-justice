<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Input;

use MonIndemnisationJustice\Entity\ProcedureJudiciaire;
use Symfony\Component\Validator\Constraints as Assert;

class ProcedureJudiciaireInput
{
    #[Assert\NotBlank(message: 'Le numéro de procédure est requis')]
    public ?string $numeroProcedure = null;
    #[Assert\NotBlank(message: 'Le nom du service enquêteur est requis')]
    public ?string $serviceEnqueteur = null;

    #[Assert\NotBlank(message: 'La numéro de téléphone du service enquêteur est requis')]
    public ?string $telephone = null;
    public ?string $juridictionOuParquet = null;
    public ?string $nomMagistrat = null;

    public static function depuisProcedureJudiciaire(ProcedureJudiciaire $procedure): self
    {
        $input = new self();
        $input->numeroProcedure = $procedure->getNumeroProcedure();
        $input->serviceEnqueteur = $procedure->getServiceEnqueteur();
        $input->telephone = $procedure->getTelephone();
        $input->juridictionOuParquet = $procedure->getJuridictionOuParquet();
        $input->nomMagistrat = $procedure->getNomMagistrat();

        return $input;
    }

    public function versProcedureJudiciaire(?ProcedureJudiciaire $procedure = null): ProcedureJudiciaire
    {
        return ($procedure ?? new ProcedureJudiciaire())
            ->setNumeroProcedure($this->numeroProcedure ?? '')
            ->setServiceEnqueteur($this->serviceEnqueteur ?? '')
            ->setTelephone($this->telephone ?? '')
            ->setJuridictionOuParquet($this->juridictionOuParquet)
            ->setNomMagistrat($this->nomMagistrat);
    }
}
