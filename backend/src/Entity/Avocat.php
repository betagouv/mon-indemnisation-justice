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

    #[ORM\Column(length: 255)]
    protected string $nom;

    #[ORM\Column(length: 255)]
    protected string $prenom;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $cabinet = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $email = null;

    #[ORM\Column(length: 3, nullable: true, enumType: Civilite::class)]
    protected ?Civilite $civilite = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $telephone = null;

    #[ORM\ManyToOne(targetEntity: Barreau::class, inversedBy: 'avocats')]
    #[ORM\JoinColumn(nullable: false)]
    protected Barreau $barreau;

    public function getNumeroCnbf(): string
    {
        return $this->numeroCnbf;
    }

    public function setNumeroCnbf(string $numeroCnbf): self
    {
        $this->numeroCnbf = $numeroCnbf;

        return $this;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getCabinet(): ?string
    {
        return $this->cabinet;
    }

    public function setCabinet(?string $cabinet): self
    {
        $this->cabinet = $cabinet;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getCivilite(): ?Civilite
    {
        return $this->civilite;
    }

    public function setCivilite(?Civilite $civilite): self
    {
        $this->civilite = $civilite;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getBarreau(): Barreau
    {
        return $this->barreau;
    }

    public function setBarreau(Barreau $barreau): self
    {
        $this->barreau = $barreau;

        return $this;
    }
}
