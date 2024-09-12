<?php

namespace App\Entity;

use DateTimeInterface;
use App\Contracts\PrejudiceInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\MappedSuperclass]
abstract class BasePrejudice implements PrejudiceInterface
{
    #[Groups('prejudice:read')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    public ?int $id = null;

    #[Groups('prejudice:read')]
    #[ORM\ManyToOne(targetEntity: Requerant::class)]
    #[ORM\JoinColumn(nullable: false)]
    protected Requerant $requerant;

    #[Groups('prejudice:read')]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    protected DateTimeInterface $dateCreation;

    #[Groups('prejudice:read')]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?DateTimeInterface $dateDeclaration = null;

    #[Groups('prejudice:read')]
    #[ORM\Column(length: 20, nullable: true)]
    private ?string $reference = null;

    #[Groups('prejudice:read')]
    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?LiasseDocumentaire $liasseDocumentaire = null;

    #[Groups('prejudice:write')]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $note = null;

    #[Groups('prejudice:write')]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $propositionIndemnisation = null;

    /*
     * Vraiment utilisé ?
    #[Groups('prejudice:write')]
    private ?int $pid = null;

    #[Groups('prejudice:write')]
    private ?LiasseDocumentaire $pLiasseDocumentaire=null;
    */

    protected PrejudiceType $type = PrejudiceType::BRIS_PORTE;

    #[Groups('prejudice:write')]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $motivationProposition = null;

    #[Groups('prejudice:read')]
    #[ORM\Column(length: 20, nullable: true)]
    /**
     * Numéro de référence raccourci, pour la recherche en ligne (comparable au numéro de réservation chez une
     * compagnie aérienne).
     */
    private ?string $raccourci = null;

    public function __construct()
    {
      $this->dateCreation = new \DateTimeImmutable();
      $this->liasseDocumentaire = new LiasseDocumentaire();
    }

    public function getPid(): ?int
    {
      return $this->getId();
    }

    public function getPLiasseDocumentaire(): ?LiasseDocumentaire
    {
      return $this->getLiasseDocumentaire();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public abstract function getType(): PrejudiceType;

    public function getRequerant(): ?Requerant
    {
        return $this->requerant;
    }

    public function setRequerant(?Requerant $requerant): static
    {
        $this->requerant = $requerant;

        return $this;
    }

    #[Groups(['prejudice:read'])]
    public function getLastStatut(): BrisPorteStatut
    {
        return null !== $this->dateDeclaration ? BrisPorteStatut::CONSTITUE : BrisPorteStatut::EN_COURS_DE_CONSTITUTION;
    }

    public function getDateCreation(): DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(DateTimeInterface $dateCreation): BasePrejudice
    {
        $this->dateCreation = $dateCreation;
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

    public function setDeclare(): self
    {
        return $this->setDateDeclaration(new \DateTimeImmutable());
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
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

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): static
    {
        $this->note = $note;

        return $this;
    }

    public function getPropositionIndemnisation(): ?string
    {
        return $this->propositionIndemnisation;
    }

    public function setPropositionIndemnisation(?string $propositionIndemnisation): static
    {
        $this->propositionIndemnisation = $propositionIndemnisation;

        return $this;
    }

    public function getMotivationProposition(): ?string
    {
        return $this->motivationProposition;
    }

    public function setMotivationProposition(?string $motivationProposition): static
    {
        $this->motivationProposition = $motivationProposition;

        return $this;
    }

    public function getRaccourci(): ?string
    {
        return $this->raccourci;
    }

    public function setRaccourci(?string $raccourci): static
    {
        $this->raccourci = $raccourci;

        return $this;
    }
}
