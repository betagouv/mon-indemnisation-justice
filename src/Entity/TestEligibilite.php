<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'eligibilite_tests')]
#[ORM\HasLifecycleCallbacks]
class TestEligibilite
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\ManyToOne(targetEntity: GeoDepartement::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'departement_code', referencedColumnName: 'code', nullable: false, onDelete: 'SET NULL')]
    public GeoDepartement $departement;

    #[ORM\Column(nullable: true)]
    public bool $estVise = false;

    #[ORM\Column(nullable: true, options: ['comments' => 'La personne recherchée réside ou est hébergée à cette adresse'])]
    public ?bool $estHebergeant = null;

    #[ORM\Column(nullable: true)]
    public ?bool $estProprietaire = null;

    #[ORM\Column(nullable: true)]
    public ?bool $aContacteAssurance = null;

    #[ORM\Column(nullable: true)]
    public ?bool $aContacteBailleur = null;

    #[ORM\OneToOne(targetEntity: Requerant::class)]
    #[ORM\JoinColumn]
    public ?Requerant $requerant = null;

    #[ORM\Column]
    public bool $estEligibleExperimentation = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    public \DateTimeInterface $dateSoumission;

    #[ORM\PrePersist]
    public function prePersist()
    {
        $this->dateSoumission = new \DateTimeImmutable();
    }

    public function toArray(): array
    {
        return [
            'departement' => $this->departement->getCode(),
            'estVise' => $this->estVise,
            'estHebergeant' => $this->estHebergeant,
            'estProprietaire' => $this->estProprietaire,
            'aContacteAssurance' => $this->aContacteAssurance,
            'aContacteBailleur' => $this->aContacteBailleur,
        ];
    }
}