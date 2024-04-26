<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\GetCollection;
use App\Contracts\PrejudiceInterface;
use App\Repository\BrisPorteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
  operations:[
    new Get(
      name: '_api_bris_porte_get'
    ),
    new GetCollection(),
    new Patch(
      name: '_api_bris_porte_patch'
    ),
  ]
)]
#[ORM\Entity(repositoryClass: BrisPorteRepository::class)]
class BrisPorte implements PrejudiceInterface
{
    use PrejudiceTrait;

    #[Groups('prejudice:read')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups('prejudice:read')]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numeroPV = null;

    #[Groups('prejudice:read')]
    #[ORM\ManyToOne(inversedBy: 'brisPortes')]
    private ?Adresse $adresse = null;

    #[Groups('prejudice:read')]
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateOperationPJ = null;

    #[Groups('prejudice:read')]
    #[ORM\Column(nullable: true,options: ['default' => false])]
    private ?bool $isPorteBlindee = null;

    #[Groups('prejudice:read')]
    #[ORM\Column(nullable: true,options: ['default' => false])]
    private ?bool $isErreurPorte = null;

    #[Groups('prejudice:read')]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $identitePersonneRecherchee = null;

    #[Groups('prejudice:read')]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomRemiseAttestation = null;

    #[Groups('prejudice:read')]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prenomRemiseAttestation = null;

    #[Groups('prejudice:read')]
    #[ORM\ManyToOne]
    private ?QualiteRequerant $qualiteRequerant = null;

    #[Groups('prejudice:read')]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $precisionRequerant = null;

    public function __construct()
    {
        $this->init();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getAdresse(): ?Adresse
    {
        return $this->adresse;
    }

    public function setAdresse(?Adresse $adresse): static
    {
        $this->adresse = $adresse;

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

    public function isPorteBlindee(): ?bool
    {
        return $this->isPorteBlindee;
    }

    public function setPorteBlindee(?bool $isPorteBlindee): static
    {
        $this->isPorteBlindee = $isPorteBlindee;

        return $this;
    }

    public function getIsPorteBlindee(): ?bool
    {
        return $this->isPorteBlindee();
    }

    public function setIsPorteBlindee(?bool $isPorteBlindee): static
    {
        $this->setPorteBlindee($isPorteBlindee);

        return $this;
    }

    public function isErreurPorte(): ?bool
    {
        return $this->isErreurPorte;
    }

    public function getIsErreurPorte(): ?bool
    {
        return $this->isErreurPorte();
    }

    public function setErreurPorte(?bool $isErreurPorte): static
    {
        $this->isErreurPorte = $isErreurPorte;

        return $this;
    }

    public function setIsErreurPorte(?bool $isErreurPorte): static
    {
        $this->setErreurPorte($isErreurPorte);

        return $this;
    }
    
    public function getIdentitePersonneRecherchee(): ?string
    {
        return $this->identitePersonneRecherchee;
    }

    public function setIdentitePersonneRecherchee(?string $identitePersonneRecherchee): static
    {
        $this->identitePersonneRecherchee = $identitePersonneRecherchee;

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

    public function getQualiteRequerant(): ?QualiteRequerant
    {
        return $this->qualiteRequerant;
    }

    public function setQualiteRequerant(?QualiteRequerant $qualiteRequerant): static
    {
        $this->qualiteRequerant = $qualiteRequerant;

        return $this;
    }

    public function getPrecisionRequerant(): ?string
    {
        return $this->precisionRequerant;
    }

    public function setPrecisionRequerant(?string $precisionRequerant): static
    {
        $this->precisionRequerant = $precisionRequerant;

        return $this;
    }

}
