<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'eligibilite_tests')]
class TestEligibilite
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\ManyToOne(targetEntity: GeoDepartement::class)]
    #[ORM\JoinColumn(name: 'departement_code', referencedColumnName: 'code', nullable: false, onDelete: 'CASCADE')]
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