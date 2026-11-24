<?php

namespace MonIndemnisationJustice\Entity;

use Doctrine\ORM\Mapping as ORM;
use MonIndemnisationJustice\Repository\AvocatRepository;

#[ORM\Entity(repositoryClass: AvocatRepository::class)]
#[ORM\Table(name: 'avocats')]
class Avocat
{
    // Code CNBF de l'avocat (avCnbfCode dans le jeu de données CNB), 6 chiffres, utilisé comme identifiant
    // naturel : c'est le numéro qu'un avocat saisit pour s'inscrire, il est stable et unique nationalement.
    #[ORM\Id]
    #[ORM\Column(length: 6)]
    protected string $numeroCnbf;

    #[ORM\ManyToOne(targetEntity: Barreau::class, inversedBy: 'avocats')]
    #[ORM\JoinColumn(nullable: false)]
    protected Barreau $barreau;

    public function getNumeroCnbf(): string {
        return $this->numeroCnbf;
    }

    public function setNumeroCnbf(string $numeroCnbf): self {
        $this->numeroCnbf = $numeroCnbf;

        return $this;
    }

    public function getBarreau(): Barreau {
        return $this->barreau;
    }

    public function setBarreau(Barreau $barreau): self {
        $this->barreau = $barreau;

        return $this;
    }
}
