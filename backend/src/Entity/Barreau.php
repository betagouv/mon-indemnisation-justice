<?php

namespace MonIndemnisationJustice\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'barreaux')]
class Barreau
{
    // Code du barreau tel que publié par le CNB (ex: "0002" pour Agen) — utilisé tel quel comme identifiant,
    // par cohérence avec les autres données géographiques/référentielles importées (GeoCommune, GeoDepartement, ...).
    #[ORM\Id]
    #[ORM\Column(length: 4)]
    protected string $id;

    #[ORM\Column(length: 255)]
    protected string $nom;

    /** @var Collection<int, Avocat> */
    #[ORM\OneToMany(targetEntity: Avocat::class, mappedBy: 'barreau')]
    protected Collection $avocats;

    public function __construct()
    {
        $this->avocats = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

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

    /**
     * @return Collection<int, Avocat>
     */
    public function getAvocats(): Collection
    {
        return $this->avocats;
    }
}
