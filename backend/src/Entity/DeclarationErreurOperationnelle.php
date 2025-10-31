<?php

namespace MonIndemnisationJustice\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Mapping as ORM;
use MonIndemnisationJustice\Entity\Metadonnees\InfosRequerant;
use Sqids\Sqids;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'declarations_erreur_operationnelle')]
#[ORM\Index(
    name: 'declaration_erreur_operationnelle_agent_idx',
    columns: ['agent_id']
)]
#[ORM\UniqueConstraint(
    name: 'declaration_erreur_operationnelle_reference_idx',
    columns: ['reference']
)]
#[ORM\HasLifecycleCallbacks]
class DeclarationErreurOperationnelle
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[Groups(['agent:detail'])]
    protected ?Uuid $id = null;

    #[ORM\Column(length: 6)]
    #[Groups(['agent:detail'])]
    protected string $reference;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: false)]
    #[Groups(['agent:detail'])]
    protected \DateTimeInterface $dateOperation;

    /**
     * @var Adresse l'adresse du logement dans laquelle
     */
    #[ORM\ManyToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'adresse_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[Groups(['agent:detail'])]
    protected Adresse $adresse;
    #[ORM\ManyToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'procedure_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[Groups(['agent:detail'])]
    protected ProcedureJudiciaire $procedure;
    #[ORM\OneToOne(targetEntity: BrisPorte::class, cascade: ['persist', 'remove'])]
    protected ?BrisPorte $dossier = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['agent:detail'])]
    protected string $commentaire = '';

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: false)]
    #[Groups(['agent:detail'])]
    protected \DateTimeInterface $dateCreation;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: false)]
    #[Groups(['agent:detail'])]
    protected \DateTimeInterface $dateSoumission;

    /**
     * @var Agent l'agent des FDO déclarant
     */
    #[ORM\ManyToOne(targetEntity: Agent::class, cascade: [])]
    #[ORM\JoinColumn(name: 'agent_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[Groups(['agent:detail'])]
    protected Agent $agent;

    #[ORM\Column(length: 20)]
    #[Groups(['agent:detail'])]
    protected string $telephone;

    #[ORM\Column(type: 'json', nullable: true)]
    protected ?array $infosRequerant;

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

    public function getDossier(): BrisPorte
    {
        return $this->dossier;
    }

    public function setDossier(BrisPorte $dossier): static
    {
        $this->dossier = $dossier;

        return $this;
    }

    public function estAttribue(): bool
    {
        return null !== $this->dossier;
    }

    public function getCommentaire(): string
    {
        return $this->commentaire;
    }

    public function setCommentaire(string $commentaire): static
    {
        $this->commentaire = $commentaire;

        return $this;
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

    public function getTelephone(): string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephoneAgent): static
    {
        $this->telephone = $telephoneAgent;

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

    public function getInfosRequerant(): ?InfosRequerant
    {
        return new InfosRequerant(...$this->infosRequerant);
    }

    public function setInfosRequerant(array|InfosRequerant $infosRequerant): DeclarationErreurOperationnelle
    {
        if ($infosRequerant instanceof InfosRequerant) {
            $this->infosRequerant = [
                'nom' => $infosRequerant->nom,
                'prenom' => $infosRequerant->prenom,
                'telephone' => $infosRequerant->telephone,
                'courriel' => $infosRequerant->courriel,
                'message' => $infosRequerant->message,
            ];
        } else {
            $this->infosRequerant = $infosRequerant;
        }

        return $this;
    }
}
