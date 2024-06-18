<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\GetCollection;
use App\Contracts\PrejudiceInterface;
use App\Repository\PrejudiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PrejudiceRepository::class)]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string')]
#[ORM\DiscriminatorMap(PrejudiceInterface::DISCRIMINATOR_MAP)]
abstract class Prejudice implements PrejudiceInterface
{
    #[Groups('prejudice:read','prejudice:write')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[Groups('prejudice:read')]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    protected ?User $requerant = null;

    #[Groups('prejudice:read')]
    protected $lastStatut;

    /**
     * @var Collection<int, Statut>
     */
    #[ORM\OneToMany(targetEntity: Statut::class, mappedBy: 'prejudice')]
    protected Collection $statuts;

    #[Groups('prejudice:read','prejudice:write')]
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    protected ?\DateTimeInterface $dateDeclaration = null;

    #[Groups('prejudice:read')]
    protected $discriminator;

    protected $discr;

    #[Groups('prejudice:read')]
    #[ORM\Column(length: 20, nullable: true)]
    private ?string $reference = null;

    #[Groups('prejudice:read')]
    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?LiasseDocumentaire $liasseDocumentaire = null;

    public function __construct()
    {
      $this->dateDeclaration = new \DateTime();
      $this->statuts = new ArrayCollection();
      $this->liasseDocumentaire = new LiasseDocumentaire();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDiscriminator(): ?string
    {
        return preg_replace("/^(.*)[#]([^#]+)$/i","$2",str_replace('\\','#',get_class($this)));
    }

    public function getDiscr(): ?string
    {
        return $this->discr;
    }

    public function setDiscr(?string $discr): self
    {
        $this->discr = $discr;

        return $this;
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

    public function getLastStatut(): Statut
    {
        return $this->statuts->last();
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

    public function getDateDeclaration(): ?\DateTimeInterface
    {
        return $this->dateDeclaration;
    }

    public function setDateDeclaration(\DateTimeInterface $dateDeclaration): static
    {
        $this->dateDeclaration = $dateDeclaration;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getLiasseDocumentaire(): ?LiasseDocumentaire
    {
        return $this->liasseDocumentaire;
    }

    public function setLiasseDocumentaire(LiasseDocumentaire $liasseDocumentaire): static
    {
        $this->liasseDocumentaire = $liasseDocumentaire;

        return $this;
    }
}