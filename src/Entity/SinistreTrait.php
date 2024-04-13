<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait SinistreTrait
{
    #[ORM\ManyToOne(inversedBy: 'sinistres')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $requerant = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Categorie $categorie = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateOperationPJ = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomRemiseAttestation = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prenomRemiseAttestation = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $qualiteRemiseAttestation = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateAttestationInformation = null;

    public function getRequerant(): ?User
    {
        return $this->requerant;
    }

    public function setRequerant(?User $requerant): static
    {
        $this->requerant = $requerant;

        return $this;
    }

    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(?Categorie $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getDateOperationPJ(): ?\DateTimeInterface
    {
        return $this->dateOperationPJ;
    }

    public function setDateOperationPJ(?\DateTimeInterface $dateOperationPJ): static
    {
        $this->dateOperationPJ = $dateOperationPJ;

        return $this;
    }

    public function getNomRemiseAttestation(): ?string
    {
        return $this->nomRemiseAttestation;
    }

    public function setNomRemiseAttestation(?string $nomRemiseAttestation): static
    {
        $this->nomRemiseAttestation = $nomRemiseAttestation;

        return $this;
    }

    public function getPrenomRemiseAttestation(): ?string
    {
        return $this->prenomRemiseAttestation;
    }

    public function setPrenomRemiseAttestation(?string $prenomRemiseAttestation): static
    {
        $this->prenomRemiseAttestation = $prenomRemiseAttestation;

        return $this;
    }

    public function getQualiteRemiseAttestation(): ?string
    {
        return $this->qualiteRemiseAttestation;
    }

    public function setQualiteRemiseAttestation(?string $qualiteRemiseAttestation): static
    {
        $this->qualiteRemiseAttestation = $qualiteRemiseAttestation;

        return $this;
    }

    public function getDateAttestationInformation(): ?\DateTimeInterface
    {
        return $this->dateAttestationInformation;
    }

    public function setDateAttestationInformation(\DateTimeInterface $dateAttestationInformation): static
    {
        $this->dateAttestationInformation = $dateAttestationInformation;

        return $this;
    }
}
