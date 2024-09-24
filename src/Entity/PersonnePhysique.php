<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use App\Repository\PersonnePhysiqueRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PersonnePhysiqueRepository::class)]
#[ApiResource(
  operations:[
    new Get(
      name: '_api_personne_physique_get',
      security: "is_granted('ROLE_REQUERANT')"
    ),
    new Patch(
      name: '_api_personne_physique_patch',
      security: "is_granted('ROLE_REQUERANT')"
  )]
)]
class PersonnePhysique
{
    #[ApiProperty(identifier: true)]
    #[Groups(['user:read','prejudice:read','user:write'])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['user:read','prejudice:read','user:write'])]
    #[ORM\Column(length: 13, nullable: true)]
    private ?string $numeroSecuriteSociale = null;

    #[Groups(['user:read','prejudice:read','user:write'])]
    #[ORM\Column(length: 2, nullable: true)]
    private ?string $codeSecuriteSociale = null;

    #[ORM\OneToOne(mappedBy: 'personnePhysique', cascade: ['persist', 'remove'])]
    private ?Requerant $compte = null;

    #[Groups(['user:read','prejudice:read','user:write'])]
    #[ORM\Column(type: 'string', length: 3, nullable: true, enumType: Civilite::class)]
    protected ?Civilite $civilite = null;

    #[Groups(['user:read','prejudice:read','user:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[Groups(['user:read','prejudice:read','user:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prenom1 = null;

    #[Groups(['user:read','prejudice:read','user:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prenom2 = null;

    #[Groups(['user:read','prejudice:read','user:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prenom3 = null;

    #[Groups(['user:read','prejudice:read','user:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telephone = null;

    #[Groups(['user:read','prejudice:read','user:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $portable = null;

    #[Groups(['user:read','prejudice:read','user:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $communeNaissance = null;

    #[Groups(['user:read','prejudice:read','user:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $paysNaissance = null;

    #[Groups(['user:read','prejudice:read','user:write'])]
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateNaissance = null;

    #[Groups(['user:read','prejudice:read','user:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomNaissance = null;

    #[Groups(['user:read','prejudice:read','user:write'])]
    #[ORM\Column(type: 'string', length: 3, nullable: true, enumType: QualiteRequerant::class)]
    protected ?QualiteRequerant $qualiteRequerant = null;

    #[Groups(['user:read','prejudice:read','user:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $precision = null;

    #[Groups(['user:read','prejudice:read','user:write'])]
    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?LiasseDocumentaire $liasseDocumentaire = null;

    #[Groups(['user:read','prejudice:read','user:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    public function __construct()
    {
      $this->liasseDocumentaire=new LiasseDocumentaire();
    }

    public function __toString()
    {
      return implode(" ",[
       $this->getCivilite()?->value,
       ucfirst(strtolower($this->getPrenom1())),
       strtoupper($this->getNomNaissance())
     ]);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCivilite(): ?Civilite
    {
        return $this->civilite;
    }

    public function setCivilite(?Civilite $civilite): self
    {
        if (null !== $civilite) {
            $this->civilite = $civilite;
        }

        return $this;
    }

    public function getNumeroSecuriteSociale(): ?string
    {
        return $this->numeroSecuriteSociale;
    }

    public function setNumeroSecuriteSociale(?string $numeroSecuriteSociale): PersonnePhysique
    {
        $this->numeroSecuriteSociale = $numeroSecuriteSociale;
        return $this;
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

    public function getPrenom1(): ?string
    {
        return $this->prenom1;
    }

    public function setPrenom1(?string $prenom1): static
    {
        $this->prenom1 = $prenom1;

        return $this;
    }

    public function getPrenom2(): ?string
    {
        return $this->prenom2;
    }

    public function setPrenom2(?string $prenom2): static
    {
        $this->prenom2 = $prenom2;

        return $this;
    }

    public function getPrenom3(): ?string
    {
        return $this->prenom3;
    }

    public function setPrenom3(?string $prenom3): static
    {
        $this->prenom3 = $prenom3;

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

    public function getPortable(): ?string
    {
        return $this->portable;
    }

    public function setPortable(?string $portable): static
    {
        $this->portable = $portable;

        return $this;
    }

    public function getCommuneNaissance(): ?string
    {
        return $this->communeNaissance;
    }

    public function setCommuneNaissance(?string $communeNaissance): static
    {
        $this->communeNaissance = $communeNaissance;

        return $this;
    }

    public function getPaysNaissance(): ?string
    {
        return $this->paysNaissance;
    }

    public function setPaysNaissance(?string $paysNaissance): static
    {
        $this->paysNaissance = $paysNaissance;

        return $this;
    }

    public function getDateNaissance(): ?\DateTimeInterface
    {
        return $this->dateNaissance;
    }

    public function setDateNaissance(?\DateTimeInterface $dateNaissance): static
    {
        $this->dateNaissance = $dateNaissance;

        return $this;
    }

    public function getNomNaissance(): ?string
    {
        return $this->nomNaissance;
    }

    public function setNomNaissance(?string $nomNaissance): static
    {
        $this->nomNaissance = $nomNaissance;

        return $this;
    }

    public function getNomComplet(): ?string
    {
        $civilite = $this->getCivilite()?->getLibelle();
        $nomNaissance = $this->getNomNaissance();
        $nom = $this->getNom();
        if($nomNaissance && $nom)
          $nom = $nom.' né.e '.$nomNaissance;
        elseif($nomNaissance)
          $nom = $nomNaissance;
        return implode(" ",[$civilite,$this->getPrenom1(),$nom]);
    }

    public function getQualiteRequerant(): ?QualiteRequerant
    {
        return $this->qualiteRequerant;
    }

    public function setQualiteRequerant(?QualiteRequerant $qualite): self
    {
        $this->qualiteRequerant = $qualite;

        return $this;
    }

    public function getPrecision(): ?string
    {
        return $this->precision;
    }

    public function setPrecision(?string $precision): static
    {
        $this->precision = $precision;

        return $this;
    }

    public function getLiasseDocumentaire(): ?LiasseDocumentaire
    {
        return $this->liasseDocumentaire;
    }

    public function setLiasseDocumentaire(LiasseDocumentaire $liasseDocumentaire): static
    {
        $this->liasseDocumentaire = $liasseDocumentaire;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }
}
