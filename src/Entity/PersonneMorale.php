<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use App\Repository\PersonneMoraleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PersonneMoraleRepository::class)]
#[ApiResource(
  operations:[
    new Get(
      name: '_api_personne_morale_get'
    ),
    new Patch(
      name: '_api_personne_morale_patch'
      #,security: "is_granted('ROLE_REQUERANT') and object.compte == user"
  )]
  )]
class PersonneMorale
{
    #[Groups(['user:read','prejudice:read','user:write'])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(mappedBy: 'personneMorale', cascade: ['persist', 'remove'])]
    private ?User $compte = null;

    #[Groups(['user:read','prejudice:read','user:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sirenSiret = null;

    #[Groups(['user:read','prejudice:read','user:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $raisonSociale = null;

    #[Groups(['user:read','prejudice:read','user:write'])]
    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?LiasseDocumentaire $liasseDocumentaire = null;

    public function __construct()
    {
      $this->liasseDocumentaire=new LiasseDocumentaire();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRaisonSociale(): ?string
    {
        return $this->raisonSociale;
    }

    public function setRaisonSociale(?string $raisonSociale): static
    {
        $this->raisonSociale = $raisonSociale;

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
            $this->compte->setPersonneMorale(null);
        }

        // set the owning side of the relation if necessary
        if ($compte !== null && $compte->getPersonneMorale() !== $this) {
            $compte->setPersonneMorale($this);
        }

        $this->compte = $compte;

        return $this;
    }

    public function getSirenSiret(): ?string
    {
        return $this->sirenSiret;
    }

    public function setSirenSiret(?string $sirenSiret): static
    {
        $this->sirenSiret = $sirenSiret;

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
}
