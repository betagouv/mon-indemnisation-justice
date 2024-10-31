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
      name: '_api_personne_morale_get',
      security: "is_granted('ROLE_REQUERANT')"
    ),
    new Patch(
      name: '_api_personne_morale_patch',
      security: "is_granted('ROLE_REQUERANT')"
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
    private ?Requerant $compte = null;

    #[Groups(['user:read','prejudice:read','user:write',])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sirenSiret = null;

    #[Groups(['user:read','prejudice:read','user:write', 'prejudice:write'])]
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

    public function getCompte(): ?Requerant
    {
        return $this->compte;
    }

    public function getSirenSiret(): ?string
    {
        return $this->sirenSiret;
    }

    public function setSirenSiret(?string $sirenSiret): PersonneMorale
    {
        $this->sirenSiret = $sirenSiret;
        return $this;
    }

    public function getRaisonSociale(): ?string
    {
        return $this->raisonSociale;
    }

    public function setRaisonSociale(?string $raisonSociale): PersonneMorale
    {
        $this->raisonSociale = $raisonSociale;
        return $this;
    }

    public function setLiasseDocumentaire(LiasseDocumentaire $liasseDocumentaire): static
    {
        $this->liasseDocumentaire = $liasseDocumentaire;

        return $this;
    }
}
