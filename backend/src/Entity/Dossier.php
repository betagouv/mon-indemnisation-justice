<?php

namespace MonIndemnisationJustice\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Patch;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Mapping as ORM;
use MonIndemnisationJustice\Event\Listener\DossierEntitylistener;
use MonIndemnisationJustice\Repository\BrisPorteRepository;
use MonIndemnisationJustice\Service\DateConvertisseur;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ApiResource(
    operations: [
        new Patch(
            uriTemplate: '/requerant/dossier/{id}',
            normalizationContext: ['groups' => ['dossier:lecture'], 'skip_null_values' => false],
            denormalizationContext: ['groups' => ['dossier:patch'], 'allow_extra_attributes' => false],
            security: "is_granted('ROLE_REQUERANT') and object.getRequerant() == user",
            name: 'requerant_dossier_api_patch'
        ),
    ]
)]
#[ORM\Entity(repositoryClass: BrisPorteRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'dossiers')]
#[ORM\EntityListeners([DossierEntitylistener::class])]
#[\AllowDynamicProperties]
class Dossier
{
    #[Groups(['dossier:lecture', 'agent:liste', 'agent:detail', 'requerant:detail'])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    public ?int $id = null;

    #[Groups(['dossier:lecture', 'agent:liste', 'agent:detail', 'requerant:detail'])]
    #[ORM\Column(length: 20, nullable: true)]
    private ?string $reference = null;

    #[ORM\Column(length: 64, enumType: DossierType::class)]
    protected DossierType $type;

    #[Groups(['dossier:lecture', 'dossier:patch', 'agent:detail'])]
    #[ORM\ManyToOne(targetEntity: Usager::class, cascade: ['persist'], inversedBy: 'dossiers')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    // L'usager qui a initié la requête
    protected Usager $usager;

    #[ORM\ManyToOne(targetEntity: Agent::class, cascade: ['persist'], inversedBy: 'dossiers')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    // L'agent rédacteur qui instruit le dossier
    protected ?Agent $redacteur = null;

    #[ORM\ManyToOne(targetEntity: PersonnePhysique::class, cascade: ['persist', 'remove'], inversedBy: 'dossiers')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    protected ?PersonnePhysique $requerantPersonnePhysique;

    #[ORM\ManyToOne(targetEntity: PersonneMorale::class, cascade: ['persist', 'remove'], inversedBy: 'dossiers')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    protected ?PersonneMorale $requerantPersonneMorale;

    #[Groups('agent:detail')]
    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $notes = null;

    #[ORM\OneToOne(targetEntity: EtatDossier::class, inversedBy: null)]
    #[ORM\JoinColumn(name: 'etat_actuel_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?EtatDossier $etatDossier = null;

    #[ORM\OneToMany(targetEntity: EtatDossier::class, mappedBy: 'dossier', cascade: ['persist', 'remove'], fetch: 'EAGER', orphanRemoval: true)]
    #[ORM\OrderBy(['dateEntree' => 'ASC'])]
    /** @var Collection<EtatDossier> */
    protected Collection $historiqueEtats;

    #[Groups('dossier:lecture')]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    protected \DateTimeInterface $dateCreation;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?\DateTimeInterface $dateDepot = null;

    #[ORM\JoinTable(name: 'document_dossiers')]
    #[ORM\JoinColumn(name: 'dossier_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToMany(targetEntity: Document::class, inversedBy: 'dossiers', cascade: ['persist', 'remove'], orphanRemoval: true)]
    /** @var Collection<Document> */
    protected Collection $documents;
    protected ?array $documentsParType = null;

    #[ORM\OneToOne(targetEntity: BrisPorte::class, inversedBy: 'dossier', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'bris_porte_id', nullable: true)]
    protected ?BrisPorte $brisPorte = null;

    #[Groups('dossier:patch')]
    #[ORM\Column(type: Types::FLOAT, precision: 10, scale: 2, nullable: true)]
    private ?float $propositionIndemnisation = null;

    public function __construct()
    {
        $this->dateCreation = new \DateTimeImmutable();
        $this->documents = new ArrayCollection([]);
        $this->historiqueEtats = new ArrayCollection([]);
    }

    #[ORM\PrePersist]
    public function onPrePersist(PrePersistEventArgs $args): void
    {
        if ($this->historiqueEtats->isEmpty()) {
            $this->changerStatut(EtatDossierType::DOSSIER_A_FINALISER, requerant: null === $this->brisPorte->getDeclarationFDO(), agent: $this->brisPorte->getDeclarationFDO()?->getAgent());
        }
    }

    #[ORM\PostLoad]
    public function onLoaded(): void
    {
        $this->documentsParType = $this->documents->reduce(
            function (array $carry, Document $document) {
                if (!isset($carry[$document->getType()->value])) {
                    $carry[$document->getType()->value] = [];
                }

                $carry[$document->getType()->value][] = $document;

                return $carry;
            },
            []
        );
    }

    public function getPid(): ?int
    {
        return $this->getId();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    #[Groups(['agent:liste'])]
    #[SerializedName('requerant')]
    public function getReferenceRequerant(): ?string
    {
        return $this->usager->getNomCourant(capital: true);
    }

    public function getRequerantPersonneMorale(): ?PersonneMorale
    {
        return $this->requerantPersonneMorale;
    }

    public function getRequerantPersonnePhysique(): ?PersonnePhysique
    {
        return $this->requerantPersonnePhysique;
    }

    public function setRequerant(PersonnePhysique|PersonneMorale $requerant): self
    {
        if ($requerant instanceof PersonneMorale) {
            $this->requerantPersonneMorale = $requerant;
        } else {
            $this->requerantPersonnePhysique = $requerant;
        }

        return $this;
    }

    /**
     * Affiche l'appellation officielle du requérant, soit:
     * - "Monsieur DUPONT Jean" pour un particulier
     * - "la société ACME représentée par Madame DUPONT, née MARTIN, Jeanne" pour un particulier
     */
    public function getNomCompletRequerant(): string
    {
        return ($this->requerantPersonneMorale
                ? "la société {$this->requerantPersonneMorale->getRaisonSociale()} représentée par " : '').$this->requerantPersonnePhysique->getNomComplet();
    }

    public function getUsager(): ?Usager
    {
        return $this->usager;
    }

    public function setUsager(?Usager $usager): self
    {
        $this->usager = $usager;

        return $this;
    }

    public function getBrisPorte(): ?BrisPorte
    {
        return $this->brisPorte;
    }

    public function setBrisPorte(?BrisPorte $brisPorte): Dossier
    {
        $this->brisPorte = $brisPorte;

        return $this;
    }

    #[Groups(['agent:detail', 'agent:liste'])]
    #[SerializedName('redacteur')]
    public function getReferenceRedacteur(): ?int
    {
        return $this->redacteur?->getId();
    }

    public function getRedacteur(): ?Agent
    {
        return $this->redacteur;
    }

    public function setRedacteur(?Agent $redacteur): Dossier
    {
        $this->redacteur = $redacteur;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): Dossier
    {
        $this->notes = $notes;

        return $this;
    }

    public function changerStatut(EtatDossierType $type, bool $requerant = false, ?Agent $agent = null, ?array $contexte = null): self
    {
        if ($requerant) {
            return $this->setEtat(EtatDossier::creerRequerant($this, $type, $contexte));
        }
        if (null !== $agent) {
            return $this->setEtat(EtatDossier::creerAgent($this, $type, $agent, $contexte));
        }

        return $this->setEtat(EtatDossier::creer($this, $type, $contexte));
    }

    public function annulerEtat(): self
    {
        $this->etatDossier = $this->historiqueEtats->get($this->historiqueEtats->count() - 2);
        $this->historiqueEtats->remove($this->historiqueEtats->count() - 1);

        return $this;
    }

    public function revenir(int $nbEtapes): self
    {
        if ($nbEtapes >= $this->historiqueEtats->count()) {
            throw new \LogicException(sprintf('Impossible de revenir plus de %d état(s) en arrière', $this->historiqueEtats->count() - 1));
        }

        /** @var EtatDossier $etatPrecedent */
        $etatPrecedent = $this->historiqueEtats->get($this->historiqueEtats->count() - ($nbEtapes + 1));

        $this->setEtat(
            EtatDossier::creer($this, $etatPrecedent->getEtat(), $etatPrecedent->getContexte())
                ->setAgent($etatPrecedent->getAgent())
                ->setRequerant($etatPrecedent->getRequerant()),
            false
        );

        return $this;
    }

    public function getLastStatut(): EtatDossier
    {
        return $this->getEtatDossier();
    }

    #[Groups(['agent:detail', 'agent:liste', 'requerant:detail'])]
    #[SerializedName('etat')]
    public function getEtatDossier(): ?EtatDossier
    {
        return $this->etatDossier;
    }

    public function getHistoriqueEtats(): Collection
    {
        return $this->historiqueEtats;
    }

    /**
     * @param EtatDossier[] $etats
     */
    public function setHistoriqueEtats(array $etats): void
    {
        foreach ($etats as $etat) {
            $this->historiqueEtats->add($etat->setDossier($this));
        }

        $this->etatDossier = $this->historiqueEtats->last();
    }

    public function estEditable(): bool
    {
        return $this->etatDossier->estEditable();
    }

    public function estAAttribuer(): bool
    {
        return $this->etatDossier->estAAttribuer();
    }

    public function estASigner(): bool
    {
        return $this->etatDossier->estASigner();
    }

    public function estSigne(): bool
    {
        return $this->etatDossier->estSigne();
    }

    public function estDecide(): bool
    {
        return $this->etatDossier->estDecide();
    }

    public function estAccepte(): bool
    {
        return $this->etatDossier->estAccepte();
    }

    public function estRejete(): bool
    {
        return $this->etatDossier->estRejete();
    }

    public function getEtat(EtatDossierType $type): ?EtatDossier
    {
        return $this->historiqueEtats->findFirst(fn (int $index, EtatDossier $etat) => $etat->getEtat() === $type);
    }

    public function estCloture(): bool
    {
        return EtatDossierType::DOSSIER_CLOTURE === $this->getEtatDossier()->getEtat();
    }

    public function estDepose(): bool
    {
        return null !== $this->getDateDeclaration();
    }

    /**
     * @deprecated préférer `estDepose()` à la place
     */
    public function estConstitue(): bool
    {
        return $this->estDepose();
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

    public function getDateDepot(): ?\DateTimeInterface
    {
        return $this->dateDepot;
    }

    public function setDateDepot(\DateTimeInterface $dateDepot): Dossier
    {
        $this->dateDepot = $dateDepot;

        return $this;
    }

    #[Groups(['agent:detail', 'agent:liste', 'requerant:detail'])]
    #[SerializedName('dateDepot')]
    public function getDateDepotMillis(): ?int
    {
        return DateConvertisseur::enMillisecondes($this->getDateDeclaration());
    }

    #[Groups('dossier:lecture')]
    public function getDateDeclaration(): ?\DateTimeInterface
    {
        return $this->getDateDepot();
    }

    public function getDateSignatureAgent(): ?\DateTimeInterface
    {
        return $this->getDateEtat(EtatDossierType::DOSSIER_OK_A_APPROUVER);
    }

    public function getDateSignatureRequerant(): ?\DateTimeInterface
    {
        return $this->getDateEtat(EtatDossierType::DOSSIER_OK_A_VERIFIER);
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function ajouterDocument(Document $document): static
    {
        $this->documents->add($document);
        $this->documentsParType[$document->getType()->value][] = $document;

        return $this;
    }

    public function supprimerDocumentsParType(DocumentType $type): void
    {
        foreach ($this->documents->filter(fn (Document $d) => $d->getType() === $type) as $document) {
            $this->documents->removeElement($document);
        }

        $this->documentsParType[$type->value] = [];
    }

    /**
     * @return Document[]|null
     */
    #[Groups(['dossier:lecture', 'agent:detail', 'requerant:detail'])]
    public function getDocuments(): array
    {
        return $this->documentsParType;
    }

    public function getDocumentsATransmettre(): Collection
    {
        return $this->documents->filter(fn (Document $document) => in_array($document->getType(), [
            DocumentType::TYPE_COURRIER_REQUERANT,
            DocumentType::TYPE_ARRETE_PAIEMENT,
            DocumentType::TYPE_CARTE_IDENTITE,
            DocumentType::TYPE_RIB,
        ]));
    }

    public function getDocumentParType(DocumentType $type): ?Document
    {
        return $this->documentsParType[$type->value][0] ?? null;
    }

    public function getOrCreatePropositionIndemnisation(): Document
    {
        return $this->getOrCreateDocument(DocumentType::TYPE_COURRIER_MINISTERE);
    }

    public function getOrCreateArretePaiement(): Document
    {
        return $this->getOrCreateDocument(DocumentType::TYPE_ARRETE_PAIEMENT);
    }

    public function getOrCreateDeclarationAcceptation(): Document
    {
        return $this->getOrCreateDocument(DocumentType::TYPE_COURRIER_REQUERANT);
    }

    /**
     * @return Document[]
     */
    public function getDocumentsParType(DocumentType $type): array
    {
        return $this->documentsParType[$type->value] ?? [];
    }

    public function getCourrierDecision(): ?Document
    {
        return $this->getDocumentParType(DocumentType::TYPE_COURRIER_MINISTERE);
    }

    public function getArretePaiement(): ?Document
    {
        return $this->getDocumentParType(DocumentType::TYPE_ARRETE_PAIEMENT);
    }

    public function getPropositionIndemnisation(): ?string
    {
        return $this->propositionIndemnisation;
    }

    #[Groups(['agent:detail', 'requerant:detail'])]
    #[SerializedName('montantIndemnisation')]
    public function getMontantIndemnisation(): ?float
    {
        return $this->propositionIndemnisation ? floatval($this->propositionIndemnisation) : null;
    }

    public function setPropositionIndemnisation(float $propositionIndemnisation): self
    {
        $this->propositionIndemnisation = $propositionIndemnisation;

        return $this;
    }

    public function getType(): DossierType
    {
        return $this->type;
    }

    public function setType(DossierType $type): Dossier
    {
        $this->type = $type;

        return $this;
    }

    public function getMotifCloture(): ?string
    {
        return $this->getEtatDossier()->getElementContexte('motif');
    }

    public function getExplicationCloture(): ?string
    {
        return $this->getEtatDossier()->getElementContexte('explication');
    }

    public function setPorteBlindee(?bool $isPorteBlindee): self
    {
        $this->isPorteBlindee = $isPorteBlindee;

        return $this;
    }

    public function getOrCreateDocument(DocumentType $type): Document
    {
        /** @var Document|null $document */
        $document = null;
        if ($type->estUnique()) {
            $document = $this->getDocumentParType($type);
        }

        return $document ?? new Document()->setType($type)->ajouterAuDossier($this);
    }

    /**
     * @param bool|null $progression est-ce qu'il s'agit d'une avancée d'un état vers le suivant ou d'un retour arrière ?
     *
     * @return $this
     */
    protected function setEtat(EtatDossier $etat, ?bool $progression = true): static
    {
        $this->historiqueEtats->add($etat);
        $this->etatDossier = $this->historiqueEtats->last();

        $this->etatDossier->postActivation($progression);

        return $this;
    }

    protected function getDateEtat(EtatDossierType $etat): ?\DateTimeInterface
    {
        return $this->historiqueEtats
            ->findFirst(
                fn (int $index, EtatDossier $e) => $etat === $e->getEtat()
            )?->getDate();
    }

    public static function brisDePorte(): Dossier
    {
        return new self()->setType(DossierType::BRIS_PORTE);
    }
}
