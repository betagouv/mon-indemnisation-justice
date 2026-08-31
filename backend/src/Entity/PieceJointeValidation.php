<?php

namespace MonIndemnisationJustice\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
class PieceJointeValidation
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    protected ?Uuid $id = null;

    #[ORM\OneToOne(targetEntity: Document::class, inversedBy: 'validation')]
    #[ORM\JoinColumn(name: 'piece_jointe_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected Document $pieceJointe;

    #[ORM\ManyToOne(targetEntity: DossierValidation::class, inversedBy: 'piecesJointes')]
    protected DossierValidation $validation;

    #[ORM\Column]
    protected bool $estRecevable;
    #[ORM\Column]
    protected ?string $commentaire = null;

    public function estRecevable(): bool
    {
        return $this->estRecevable;
    }

    public function setRecevable(bool $estRecevable): PieceJointeValidation
    {
        $this->estRecevable = $estRecevable;

        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): PieceJointeValidation
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    public function getPieceJointe(): Document
    {
        return $this->pieceJointe;
    }

    public function setPieceJointe(Document $pieceJointe): PieceJointeValidation
    {
        $this->pieceJointe = $pieceJointe;

        return $this;
    }

    public function getValidation(): DossierValidation
    {
        return $this->validation;
    }

    public function setValidation(DossierValidation $validation): PieceJointeValidation
    {
        $this->validation = $validation;

        return $this;
    }
}
