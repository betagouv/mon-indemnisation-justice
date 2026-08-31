<?php

namespace MonIndemnisationJustice\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
class DossierValidation
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    protected ?Uuid $id = null;

    #[ORM\ManyToOne(targetEntity: Dossier::class, inversedBy: 'validations')]
    #[ORM\JoinColumn(name: 'dossier_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected Dossier $dossier;

    /** @var Collection<PieceJointeValidation> */
    #[ORM\OneToMany(targetEntity: PieceJointeValidation::class, mappedBy: 'validation', cascade: ['persist', 'remove'], orphanRemoval: true)]
    protected Collection $piecesJointes;

    #[ORM\Column]
    protected bool $estRecevable;
    #[ORM\Column(nullable: true)]
    protected ?string $commentaire = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    public ?\DateTimeInterface $date = null;

    #[ORM\ManyToOne(targetEntity: Agent::class, cascade: [])]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    public ?Agent $validateur = null;

    public function __construct()
    {
        $this->piecesJointes = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getDossier(): Dossier
    {
        return $this->dossier;
    }

    public function setDossier(Dossier $dossier): DossierValidation
    {
        $this->dossier = $dossier;

        return $this;
    }

    public function estRecevable(): bool
    {
        return $this->estRecevable;
    }

    public function setRecevable(bool $estRecevable): DossierValidation
    {
        $this->estRecevable = $estRecevable;

        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): DossierValidation
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    public function getValidationPieceJointe(Document $document): ?PieceJointeValidation
    {
        if ($this->dossier->getId() !== $document->getDossier()->getId()) {
            return null;
        }

        return $this->piecesJointes->findFirst(fn (int $key, PieceJointeValidation $validation) => $validation->getPieceJointe() === $document);
    }

    /**
     * @param array<PieceJointeValidation> $piecesJointes
     *
     * @return $this
     */
    public function setPiecesJointes(array $piecesJointes): DossierValidation
    {
        $this->piecesJointes->clear();
        foreach ($piecesJointes as $pieceJointe) {
            $this->piecesJointes->add($pieceJointe);
            $pieceJointe->setValidation($this);
        }

        return $this;
    }
}
