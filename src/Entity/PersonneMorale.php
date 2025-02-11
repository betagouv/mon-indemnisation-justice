<?php

namespace MonIndemnisationJustice\Entity;

use Doctrine\ORM\Mapping as ORM;
use MonIndemnisationJustice\Repository\PersonneMoraleRepository;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PersonneMoraleRepository::class)]
class PersonneMorale
{
    #[Groups(['dossier:lecture', 'dossier:patch'])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(mappedBy: 'personneMorale', cascade: ['persist', 'remove'])]
    private ?Requerant $compte = null;

    #[Groups(['dossier:lecture', 'dossier:patch'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sirenSiret = null;

    #[Groups(['dossier:lecture', 'dossier:patch'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $raisonSociale = null;

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
}
