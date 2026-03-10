<?php

namespace MonIndemnisationJustice\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use MonIndemnisationJustice\Repository\PersonneMoraleRepository;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Table(name: 'personnes_morales')]
#[ORM\Entity(repositoryClass: PersonneMoraleRepository::class)]
class PersonneMorale
{
    #[Groups(['dossier:lecture', 'dossier:patch'])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToMany(targetEntity: Dossier::class, mappedBy: 'requerantPersonneMorale')]
    /** @var Collection<Dossier> */
    protected Collection $dossiers;

    #[Groups(['dossier:lecture', 'dossier:patch'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sirenSiret = null;

    #[Groups(['dossier:lecture', 'dossier:patch'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $raisonSociale = null;

    #[ORM\OneToOne(targetEntity: Personne::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'representant_legal_id', referencedColumnName: 'id')]
    protected ?Personne $representantLegal;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getRepresentantLegal(): ?Personne
    {
        return $this->representantLegal;
    }

    public function setRepresentantLegal(?Personne $representantLegal): PersonneMorale
    {
        $this->representantLegal = $representantLegal;

        return $this;
    }
}
