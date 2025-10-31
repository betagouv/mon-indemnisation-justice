<?php

namespace MonIndemnisationJustice\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'procedures_judiciaires')]
class ProcedureJudiciaire
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    protected ?Uuid $id = null;
    #[ORM\Column]
    protected string $numeroProcedure;
    #[ORM\Column]
    protected string $serviceEnqueteur;
    #[ORM\Column(nullable: true)]
    protected string $juridictionOuParquet;
    #[ORM\Column(nullable: true)]
    protected string $nomMagistrat;

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(?Uuid $id): ProcedureJudiciaire
    {
        $this->id = $id;

        return $this;
    }

    public function getNumeroProcedure(): string
    {
        return $this->numeroProcedure;
    }

    public function setNumeroProcedure(string $numeroProcedure): ProcedureJudiciaire
    {
        $this->numeroProcedure = $numeroProcedure;

        return $this;
    }

    public function getServiceEnqueteur(): string
    {
        return $this->serviceEnqueteur;
    }

    public function setServiceEnqueteur(string $serviceEnqueteur): ProcedureJudiciaire
    {
        $this->serviceEnqueteur = $serviceEnqueteur;

        return $this;
    }

    public function getJuridictionOuParquet(): string
    {
        return $this->juridictionOuParquet;
    }

    public function setJuridictionOuParquet(string $juridictionOuParquet): ProcedureJudiciaire
    {
        $this->juridictionOuParquet = $juridictionOuParquet;

        return $this;
    }

    public function getNomMagistrat(): string
    {
        return $this->nomMagistrat;
    }

    public function setNomMagistrat(string $nomMagistrat): ProcedureJudiciaire
    {
        $this->nomMagistrat = $nomMagistrat;

        return $this;
    }
}
