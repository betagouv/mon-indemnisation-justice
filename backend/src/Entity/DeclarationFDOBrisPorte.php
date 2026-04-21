<?php

namespace MonIndemnisationJustice\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Sqids\Sqids;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'declarations_fdo_bris_porte')]
#[ORM\Index(
    name: 'declarations_fdo_bris_porte_agent_idx',
    columns: ['agent_id']
)]
#[ORM\UniqueConstraint(
    name: 'declarations_fdo_bris_porte_reference_idx',
    columns: ['reference']
)]
#[ORM\HasLifecycleCallbacks]
class DeclarationFDOBrisPorte
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    protected ?Uuid $id = null;

    #[ORM\Column(length: 6)]
    protected string $reference;

    #[ORM\Column(name: 'est_erreur', length: 6, enumType: DeclarationFDOBrisPorteErreurType::class)]
    protected DeclarationFDOBrisPorteErreurType $estErreur;

    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $descriptionErreur = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: false)]
    protected \DateTimeInterface $dateOperation;

    /**
     * @var Adresse l'adresse du logement dans laquelle
     */
    #[ORM\ManyToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'adresse_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected Adresse $adresse;

    #[ORM\ManyToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'procedure_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected ProcedureJudiciaire $procedure;

    #[ORM\ManyToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'coordonnees_requerant_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    protected ?CoordonneesRequerant $coordonneesRequerant = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $precisionsRequerant = null;

    #[ORM\OneToOne(targetEntity: Dossier::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    protected ?Dossier $dossier = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    protected \DateTimeInterface $dateCreation;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    protected \DateTimeInterface $dateSoumission;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    protected ?\DateTimeInterface $dateSuppression = null;

    /**
     * @var Agent l'agent des FDO déclarant
     */
    #[ORM\ManyToOne(targetEntity: Agent::class, cascade: [])]
    #[ORM\JoinColumn(name: 'agent_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected Agent $agent;

    #[ORM\JoinTable(name: 'declaration_fdo_bris_porte_pieces_jointes')]
    #[ORM\JoinColumn(name: 'declaration_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'document', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: Document::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    /** @var Collection<Document> */
    protected Collection $piecesJointes;

    public function __construct()
    {
        $this->piecesJointes = new ArrayCollection([]);
    }

    #[ORM\PrePersist]
    public function onPrePersist(PrePersistEventArgs $args): void
    {
        $generateurShortId = new Sqids(alphabet: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', minLength: 6);

        $this->reference = substr($generateurShortId->encode([$this->agent->getId(), $this->dateCreation->getTimestamp() % 1000]), 0, 6);
        $this->dateSoumission = new \DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(?Uuid $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getEstErreur(): DeclarationFDOBrisPorteErreurType
    {
        return $this->estErreur;
    }

    public function setEstErreur(DeclarationFDOBrisPorteErreurType $estErreur): DeclarationFDOBrisPorte
    {
        $this->estErreur = $estErreur;

        return $this;
    }

    public function getDescriptionErreur(): ?string
    {
        return $this->descriptionErreur;
    }

    public function setDescriptionErreur(?string $descriptionErreur): DeclarationFDOBrisPorte
    {
        $this->descriptionErreur = $descriptionErreur;

        return $this;
    }

    public function getDateOperation(): \DateTimeInterface
    {
        return $this->dateOperation;
    }

    public function setDateOperation(\DateTimeInterface $dateOperation): static
    {
        $this->dateOperation = $dateOperation;

        return $this;
    }

    public function getAdresse(): Adresse
    {
        return $this->adresse;
    }

    public function setAdresse(Adresse $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getProcedure(): ProcedureJudiciaire
    {
        return $this->procedure;
    }

    public function setProcedure(ProcedureJudiciaire $procedure): static
    {
        $this->procedure = $procedure;

        return $this;
    }

    public function getDossier(): ?Dossier
    {
        return $this->dossier;
    }

    public function setDossier(Dossier $dossier): static
    {
        $this->dossier = $dossier;

        return $this;
    }

    public function estAttribue(): bool
    {
        return null !== $this->dossier;
    }

    public function getAgent(): Agent
    {
        return $this->agent;
    }

    public function setAgent(Agent $agent): static
    {
        $this->agent = $agent;

        return $this;
    }

    public function getDateCreation(): \DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getDateSoumission(): \DateTimeInterface
    {
        return $this->dateSoumission;
    }

    public function setDateSoumission(\DateTimeInterface $dateSoumission): static
    {
        $this->dateSoumission = $dateSoumission;

        return $this;
    }

    public function getCoordonneesRequerant(): ?CoordonneesRequerant
    {
        return $this->coordonneesRequerant;
    }

    public function setCoordonneesRequerant(?CoordonneesRequerant $coordonneesRequerant = null): DeclarationFDOBrisPorte
    {
        $this->coordonneesRequerant = $coordonneesRequerant;

        return $this;
    }

    public function getPrecisionsRequerant(): ?string
    {
        return $this->precisionsRequerant;
    }

    public function setPrecisionsRequerant(?string $precisionsRequerant): DeclarationFDOBrisPorte
    {
        $this->precisionsRequerant = $precisionsRequerant;

        return $this;
    }

    public function getDateSuppression(): \DateTimeInterface
    {
        return $this->dateSuppression;
    }

    public function setDateSuppression(\DateTimeInterface $dateSuppression): DeclarationFDOBrisPorte
    {
        $this->dateSuppression = $dateSuppression;

        return $this;
    }

    public function getPiecesJointes(): Collection
    {
        return $this->piecesJointes;
    }

    public function setPiecesJointes(array|Collection $piecesJointes): DeclarationFDOBrisPorte
    {
        foreach ($piecesJointes as $pieceJointe) {
            $this->piecesJointes->add($pieceJointe);
        }

        return $this;
    }
}
