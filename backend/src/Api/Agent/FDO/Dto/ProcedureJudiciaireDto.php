<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Dto;

use MonIndemnisationJustice\Entity\ProcedureJudiciaire;
use Symfony\Component\Validator\Constraints as Assert;

class ProcedureJudiciaireDto
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
        $dto = new self();
        $dto->numeroProcedure = $procedure->getNumeroProcedure();
        $dto->serviceEnqueteur = $procedure->getServiceEnqueteur();
        $dto->telephone = $procedure->getTelephone();
        $dto->juridictionOuParquet = $procedure->getJuridictionOuParquet();
        $dto->nomMagistrat = $procedure->getNomMagistrat();

        return $dto;
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
