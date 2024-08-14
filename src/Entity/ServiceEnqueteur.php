<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use App\Repository\ServiceEnqueteurRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ServiceEnqueteurRepository::class)]
#[ApiResource(
  operations:[
    new Get(name: '_api_service_enqueteur_get'),
    new Patch(name: '_api_service_enqueteur_patch')
  ]
)]
class ServiceEnqueteur
{
    #[Groups(['prejudice:read','prejudice:write'])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['prejudice:read','prejudice:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[Groups(['prejudice:read','prejudice:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telephone = null;

    #[Groups(['prejudice:read','prejudice:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $courriel = null;

    #[Groups(['prejudice:read','prejudice:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numeroPV = null;

    #[Groups(['prejudice:read','prejudice:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $juridiction = null;

    #[Groups(['prejudice:read','prejudice:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $magistrat = null;

    #[Groups(['prejudice:read','prejudice:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numeroParquet = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getCourriel(): ?string
    {
        return $this->courriel;
    }

    public function setCourriel(?string $courriel): static
    {
        $this->courriel = $courriel;

        return $this;
    }

    public function getNumeroPV(): ?string
    {
        return $this->numeroPV;
    }

    public function setNumeroPV(?string $numeroPV): static
    {
        $this->numeroPV = $numeroPV;

        return $this;
    }

    public function getJuridiction(): ?string
    {
        return $this->juridiction;
    }

    public function setJuridiction(?string $juridiction): static
    {
        $this->juridiction = $juridiction;

        return $this;
    }

    public function getMagistrat(): ?string
    {
        return $this->magistrat;
    }

    public function setMagistrat(?string $magistrat): static
    {
        $this->magistrat = $magistrat;

        return $this;
    }

    public function getNumeroParquet(): ?string
    {
        return $this->numeroParquet;
    }

    public function setNumeroParquet(?string $numeroParquet): static
    {
        $this->numeroParquet = $numeroParquet;

        return $this;
    }
}
