<?php

namespace MonIndemnisationJustice\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'coordonnees_requerant')]
#[Groups(['agent:detail'])]
class CoordonneesRequerant
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    protected ?int $id;

    #[ORM\Column(type: 'string', length: 3, nullable: true, enumType: Civilite::class)]
    protected Civilite $civilite;
    #[ORM\Column(length: 255)]
    protected string $nom;
    #[ORM\Column(length: 255)]
    protected string $prenom;
    #[ORM\Column(length: 255)]
    protected string $telephone;
    #[ORM\Column(length: 255)]
    protected string $courriel;

    public function __construct() {}

    public function getCivilite(): ?Civilite
    {
        return $this->civilite;
    }

    public function setCivilite(?Civilite $civilite): CoordonneesRequerant
    {
        $this->civilite = $civilite;

        return $this;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): CoordonneesRequerant
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): CoordonneesRequerant
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getTelephone(): string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): CoordonneesRequerant
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getCourriel(): string
    {
        return $this->courriel;
    }

    public function setCourriel(string $courriel): CoordonneesRequerant
    {
        $this->courriel = $courriel;

        return $this;
    }
}
