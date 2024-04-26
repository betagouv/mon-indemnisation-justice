<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait PrejudiceTrait
{
    #[ORM\ManyToOne(inversedBy: 'prejudices')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $requerant = null;

    #[Groups('prejudice:read')]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Categorie $categorie = null;

    #[Groups('prejudice:read')]
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateAttestationInformation = null;

    #[Groups('prejudice:read')]
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateDeclaration = null;

    #[Groups('prejudice:read')]
    private $lastStatut;

    public function init(): void
    {
      $this->statuts = new ArrayCollection();
      $this->dateDeclaration = new \DateTime();
    }

    /**
     * @var Collection<int, Statut>
     */
    #[ORM\OneToMany(targetEntity: Statut::class, mappedBy: 'prejudice')]
    private Collection $statuts;

    public function getLastStatut(): Statut
    {
        return $this->statuts->last();
    }

    public function getRequerant(): ?User
    {
        return $this->requerant;
    }

    public function setRequerant(?User $requerant): static
    {
        $this->requerant = $requerant;

        return $this;
    }

    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(?Categorie $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getDateAttestationInformation(): ?\DateTimeInterface
    {
        return $this->dateAttestationInformation;
    }

    public function setDateAttestationInformation(\DateTimeInterface $dateAttestationInformation): static
    {
        $this->dateAttestationInformation = $dateAttestationInformation;

        return $this;
    }

    public function getDateDeclaration(): ?\DateTimeInterface
    {
        return $this->dateDeclaration;
    }

    public function setDateDeclaration(\DateTimeInterface $dateDeclaration): static
    {
        $this->dateDeclaration = $dateDeclaration;

        return $this;
    }

    /**
     * @return Collection<int, Statut>
     */
    public function getStatuts(): Collection
    {
        return $this->statuts;
    }

    public function addStatut(Statut $statut): static
    {
        if (!$this->statuts->contains($statut)) {
            $this->statuts->add($statut);
            $statut->setBrisPorte($this);
        }

        return $this;
    }

    public function removeStatut(Statut $statut): static
    {
        if ($this->statuts->removeElement($statut)) {
            // set the owning side to null (unless already changed)
            if ($statut->getBrisPorte() === $this) {
                $statut->setBrisPorte(null);
            }
        }

        return $this;
    }
}
