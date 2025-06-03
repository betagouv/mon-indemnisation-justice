<?php

namespace MonIndemnisationJustice\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use MonIndemnisationJustice\Repository\AdresseRepository;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AdresseRepository::class)]
class Adresse
{
    #[Groups(['dossier:lecture', 'dossier:patch'])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['dossier:lecture', 'dossier:patch', 'agent:detail', 'requerant:detail', 'agent:liste'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ligne1 = null;

    #[Groups(['dossier:lecture', 'dossier:patch', 'agent:detail', 'requerant:detail', 'agent:liste'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ligne2 = null;

    #[Groups(['dossier:lecture', 'dossier:patch', 'agent:detail', 'requerant:detail', 'agent:liste'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $codePostal = null;

    #[Groups(['dossier:lecture', 'dossier:patch', 'agent:detail', 'requerant:detail', 'agent:liste'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $localite = null;

    #[ORM\Column(name: 'commune_code', length: 5, nullable: true)]
    #[ORM\ManyToOne(targetEntity: GeoCommune::class)]
    protected ?GeoCommune $commune = null;

    /**
     * @var Collection<int, BrisPorte>
     */
    #[ORM\OneToMany(targetEntity: BrisPorte::class, mappedBy: 'adresse')]
    private Collection $brisPortes;

    public function __toString()
    {
        return $this->getLibelle();
    }

    public function getLibelle(): string
    {
        return "{$this->getLigne1()} {$this->getCodePostal()} {$this->getLocalite()}";
    }

    public function __construct()
    {
        $this->brisPortes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLigne1(): ?string
    {
        return $this->ligne1;
    }

    public function setLigne1(?string $ligne1): static
    {
        $this->ligne1 = $ligne1;

        return $this;
    }

    public function getLigne2(): ?string
    {
        return $this->ligne2;
    }

    public function setLigne2(?string $ligne2): static
    {
        $this->ligne2 = $ligne2;

        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function getCodeDepartemental(): ?string
    {
        return substr($this->codePostal, 0, strlen($this->codePostal) - 3);
    }

    public function setCodePostal(?string $codePostal): static
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    public function getLocalite(): ?string
    {
        return $this->localite;
    }

    public function setLocalite(?string $localite): static
    {
        $this->localite = $localite;

        return $this;
    }

    public function getCommune(): ?GeoCommune
    {
        return $this->commune;
    }

    public function setCommune(?GeoCommune $commune): Adresse
    {
        $this->commune = $commune;

        return $this;
    }

    /**
     * @return Collection<int, BrisPorte>
     */
    public function getBrisPortes(): Collection
    {
        return $this->brisPortes;
    }

    public function addBrisPorte(BrisPorte $brisPorte): static
    {
        if (!$this->brisPortes->contains($brisPorte)) {
            $this->brisPortes->add($brisPorte);
            $brisPorte->setAdresse($this);
        }

        return $this;
    }

    public function removeBrisPorte(BrisPorte $brisPorte): static
    {
        if ($this->brisPortes->removeElement($brisPorte)) {
            // set the owning side to null (unless already changed)
            if ($brisPorte->getAdresse() === $this) {
                $brisPorte->setAdresse(null);
            }
        }

        return $this;
    }
}
