<?php

namespace MonIndemnisationJustice\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use MonIndemnisationJustice\Repository\PersonneMoraleRepository;

#[ORM\Table(name: 'personnes_morales')]
#[ORM\Entity(repositoryClass: PersonneMoraleRepository::class)]
class PersonneMorale
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToMany(targetEntity: Dossier::class, mappedBy: 'requerantPersonneMorale')]
    /** @var Collection<Dossier> */
    protected Collection $dossiers;

    #[ORM\Column(length: 32, enumType: PersonneMoraleType::class, options: ['default' => PersonneMoraleType::ENTREPRISE_PRIVEE])]
    protected PersonneMoraleType $type;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sirenSiret = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $raisonSociale = null;

    #[ORM\ManyToOne(Adresse::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    protected ?Adresse $adresse = null;

    #[ORM\OneToOne(targetEntity: Personne::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'representant_legal_id', referencedColumnName: 'id')]
    protected ?Personne $representantLegal = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): PersonneMoraleType
    {
        return $this->type;
    }

    public function setType(PersonneMoraleType $type): PersonneMorale
    {
        $this->type = $type;

        return $this;
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

    public function getAdresse(): ?Adresse
    {
        return $this->adresse;
    }

    public function setAdresse(?Adresse $adresse): PersonneMorale
    {
        $this->adresse = $adresse;

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
