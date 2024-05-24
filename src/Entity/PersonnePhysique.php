<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
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
      name: '_api_personne_physique_get'
    ),
    new Patch(
      name: '_api_personne_physique_patch'
      #,security: "is_granted('ROLE_REQUERANT') and object.compte == user"
  )]
)]
class PersonnePhysique
{
    #[Groups(['user:read','prejudice:read','user:write'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['user:read','prejudice:read','user:write'])]
    #[ORM\Column(length: 13, nullable: true)]
    private ?string $numeroSecuriteSociale = null;

    #[Groups(['user:read','prejudice:read','user:write'])]
    #[ORM\Column(length: 2, nullable: true)]
    private ?string $codeSecuriteSociale = null;

    #[ORM\OneToOne(mappedBy: 'personnePhysique', cascade: ['persist', 'remove'])]
    private ?User $compte = null;

    #[Groups(['user:read','prejudice:read','user:write'])]
    #[ORM\ManyToOne]
    private ?Civilite $civilite = null;

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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroSecuriteSociale(): ?string
    {
        return $this->numeroSecuriteSociale;
    }

    public function setNumeroSecuriteSociale(?string $numeroSecuriteSociale): static
    {
        $this->numeroSecuriteSociale = $numeroSecuriteSociale;

        return $this;
    }

    public function getCodeSecuriteSociale(): ?string
    {
        return $this->codeSecuriteSociale;
    }

    public function setCodeSecuriteSociale(?string $codeSecuriteSociale): static
    {
        $this->codeSecuriteSociale = $codeSecuriteSociale;

        return $this;
    }

    public function getCompte(): ?User
    {
        return $this->compte;
    }

    public function setCompte(?User $compte): static
    {
        // unset the owning side of the relation if necessary
        if ($compte === null && $this->compte !== null) {
            $this->compte->setPersonnePhysique(null);
        }

        // set the owning side of the relation if necessary
        if ($compte !== null && $compte->getPersonnePhysique() !== $this) {
            $compte->setPersonnePhysique($this);
        }

        $this->compte = $compte;

        return $this;
    }

    public function getCivilite(): ?Civilite
    {
        return $this->civilite;
    }

    public function setCivilite(?Civilite $civilite): static
    {
        $this->civilite = $civilite;

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
        $civilite = $this->getCivilite() ? $this->getCivilite()->getLibelle() : null;
        return implode(" ",[$civilite,$this->getPrenom1(),$this->getNom()]);
    }
}
