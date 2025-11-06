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
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

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
#[ORM\Table(name: 'bris_porte')]
#[ORM\EntityListeners([DossierEntitylistener::class])]
#[\AllowDynamicProperties]
class BrisPorte
{
    #[Groups(['dossier:lecture', 'agent:liste', 'agent:detail', 'requerant:detail'])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    public ?int $id = null;

    #[Groups(['dossier:lecture', 'dossier:patch', 'agent:detail'])]
    #[ORM\ManyToOne(targetEntity: Requerant::class, cascade: [], inversedBy: 'dossiers')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    protected Requerant $requerant;

    #[ORM\ManyToOne(targetEntity: Agent::class, cascade: ['persist'], inversedBy: 'dossiers')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    protected ?Agent $redacteur = null;

    #[Groups('agent:detail')]
    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $notes = null;

    #[ORM\OneToOne(targetEntity: EtatDossier::class, inversedBy: null)]
    #[ORM\JoinColumn(name: 'etat_actuel_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?EtatDossier $etatDossier = null;

    #[ORM\OneToMany(targetEntity: EtatDossier::class, mappedBy: 'dossier', cascade: ['persist', 'remove'], fetch: 'EAGER')]
    #[ORM\OrderBy(['dateEntree' => 'ASC'])]
    /** @var Collection<EtatDossier> */
    protected Collection $historiqueEtats;

    #[ORM\OneToMany(targetEntity: CourrierDossier::class, mappedBy: 'dossier', cascade: ['persist', 'remove'], fetch: 'LAZY')]
    #[ORM\OrderBy(['dateCreation' => 'ASC'])]
    /** @var Collection<CourrierDossier> */
    protected Collection $historiqueCourriers;

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

    #[Groups(['dossier:lecture', 'dossier:patch'])]
    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $numeroPV = null;

    #[Groups(['agent:detail', 'requerant:detail'])]
    #[ORM\OneToOne(targetEntity: TestEligibilite::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    protected ?TestEligibilite $testEligibilite = null;

    #[Groups(['dossier:lecture', 'dossier:patch'])]
    #[ORM\Column(type: 'string', length: 3, nullable: true, enumType: QualiteRequerant::class)]
    protected ?QualiteRequerant $qualiteRequerant = null;

    #[Groups(['dossier:patch', 'agent:detail', 'requerant:detail'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $descriptionRequerant;

    #[Groups(['agent:detail'])]
    #[ORM\Column(length: 2, nullable: true, enumType: TypeInstitutionSecuritePublique::class)]
    protected ?TypeInstitutionSecuritePublique $typeInstitutionSecuritePublique = null;

    #[Groups(['agent:liste', 'agent:detail'])]
    #[ORM\Column(length: 20, nullable: true, enumType: TypeAttestation::class)]
    protected ?TypeAttestation $typeAttestation = null;

    #[Groups(['dossier:lecture', 'agent:liste', 'agent:detail', 'requerant:detail'])]
    #[ORM\Column(length: 20, nullable: true)]
    private ?string $reference = null;

    #[Groups('dossier:patch')]
    #[ORM\Column(type: Types::FLOAT, precision: 10, scale: 2, nullable: true)]
    private ?float $propositionIndemnisation = null;

    #[Groups(['dossier:lecture', 'dossier:patch', 'agent:detail', 'agent:liste', 'requerant:detail'])]
    #[ORM\ManyToOne(cascade: ['persist', 'remove'], inversedBy: 'brisPortes')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Adresse $adresse;

    #[Groups(['dossier:lecture', 'dossier:patch'])]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateOperationPJ = null;

    #[Groups(['dossier:lecture', 'dossier:patch', 'agent:detail', 'requerant:detail'])]
    #[SerializedName('estPorteBlindee')]
    #[ORM\Column(options: ['default' => false])]
    private bool $isPorteBlindee = false;

    #[Groups(['dossier:lecture', 'dossier:patch'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $precisionRequerant = null;

    public function __construct()
    {
        $this->dateCreation = new \DateTimeImmutable();
        $this->adresse = new Adresse();
        $this->documents = new ArrayCollection([]);
        $this->historiqueEtats = new ArrayCollection([]);
        $this->historiqueCourriers = new ArrayCollection([]);
    }

    #[ORM\PrePersist]
    public function onPrePersist(PrePersistEventArgs $args): void
    {
        if ($this->historiqueEtats->isEmpty()) {
            $this->changerStatut(EtatDossierType::DOSSIER_A_FINALISER, requerant: true);
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
        return $this->requerant->getNomCourant(capital: true);
    }

    public function getRequerant(): ?Requerant
    {
        return $this->requerant;
    }

    public function setRequerant(?Requerant $requerant): self
    {
        $this->requerant = $requerant;

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

    public function setRedacteur(?Agent $redacteur): BrisPorte
    {
        $this->redacteur = $redacteur;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): BrisPorte
    {
        $this->notes = $notes;

        return $this;
    }

    public function changerStatut(EtatDossierType $type, bool $requerant = false, ?Agent $agent = null, ?array $contexte = null): self
    {
        if ($requerant) {
            $this->historiqueEtats->add(EtatDossier::creerRequerant($this, $type, $contexte));
        } elseif (null !== $agent) {
            $this->historiqueEtats->add(EtatDossier::creerAgent($this, $type, $agent, $contexte));
        } else {
            $this->historiqueEtats->add(EtatDossier::creer($this, $type, $contexte));
        }

        $this->etatDossier = $this->historiqueEtats->last();
        $this->etatDossier->postActivation();

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

    public function revenirEtatPrecedent(): static
    {
        if ($this->historiqueEtats->count() > 1) {
            $this->historiqueEtats->removeElement($this->historiqueEtats->last());
            $this->etatDossier = $this->historiqueEtats->last();
        }

        return $this;
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

    public function setDateDepot(\DateTimeInterface $dateDepot): BrisPorte
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

    public function ajouterDocument(Document $document): void
    {
        $this->documents->add($document);
        $this->documentsParType[$document->getType()->value][] = $document;
    }

    public function supprimerDocumentsParType(DocumentType $type): void
    {
        foreach ($this->documents->filter(fn (Document $d) => $d->getType() === $type) as $document) {
            $this->documents->removeElement($document);
        }

        $this->documentsParType[$type->value] = [];
    }

    /**
     * @return null|Document[]
     */
    #[Groups(['dossier:lecture', 'agent:detail', 'requerant:detail'])]
    public function getDocuments(): array
    {
        return $this->documentsParType;
    }

    public function getRawDocuments(): Collection
    {
        return $this->documents;
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

    public function getType(): PrejudiceType
    {
        return PrejudiceType::BRIS_PORTE;
    }

    public function getMotifCloture(): ?string
    {
        return $this->getEtatDossier()->getElementContexte('motif');
    }

    public function getExplicationCloture(): ?string
    {
        return $this->getEtatDossier()->getElementContexte('explication');
    }

    public function getNumeroPV(): ?string
    {
        return $this->numeroPV;
    }

    public function setNumeroPV(?string $numeroPV): self
    {
        $this->numeroPV = $numeroPV;

        return $this;
    }

    #[Groups('agent:liste')]
    #[SerializedName('adresse')]
    public function getReferenceAdresse(): string
    {
        return $this->adresse->getLibelle();
    }

    public function getAdresse(): ?Adresse
    {
        return $this->adresse;
    }

    public function setAdresse(?Adresse $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getDateOperationPJ(): ?\DateTimeInterface
    {
        return $this->dateOperationPJ;
    }

    #[Groups(['agent:detail', 'requerant:detail'])]
    #[SerializedName('dateOperation')]
    public function getDateOperationPJMillis(): ?int
    {
        return DateConvertisseur::enMillisecondes($this->dateOperationPJ);
    }

    public function setDateOperationPJ(?\DateTimeInterface $dateOperationPJ): self
    {
        $this->dateOperationPJ = $dateOperationPJ;

        return $this;
    }

    public function getTestEligibilite(): ?TestEligibilite
    {
        return $this->testEligibilite;
    }

    #[Groups(['agent:liste'])]
    #[SerializedName('estEligible')]
    public function getEstEligible(): ?bool
    {
        return $this->testEligibilite?->estEligible();
    }

    public function setTestEligibilite(?TestEligibilite $testEligibilite): BrisPorte
    {
        $this->testEligibilite = $testEligibilite;

        return $this;
    }

    public function isPorteBlindee(): ?bool
    {
        return $this->isPorteBlindee;
    }

    public function setPorteBlindee(?bool $isPorteBlindee): self
    {
        $this->isPorteBlindee = $isPorteBlindee;

        return $this;
    }

    public function getIsPorteBlindee(): ?bool
    {
        return $this->isPorteBlindee();
    }

    public function setIsPorteBlindee(?bool $isPorteBlindee): self
    {
        $this->setPorteBlindee($isPorteBlindee);

        return $this;
    }

    public function getAdressePlaintext(): string
    {
        return (string) $this->getAdresse();
    }

    public function getPrecisionRequerant(): ?string
    {
        return $this->precisionRequerant;
    }

    public function setPrecisionRequerant(?string $precisionRequerant): self
    {
        $this->precisionRequerant = $precisionRequerant;

        return $this;
    }

    public function getQualiteRequerant(): ?QualiteRequerant
    {
        return $this->qualiteRequerant;
    }

    public function setQualiteRequerant(?QualiteRequerant $qualiteRequerant): BrisPorte
    {
        $this->qualiteRequerant = $qualiteRequerant;

        return $this;
    }

    public function getDescriptionRequerant(): ?string
    {
        return $this->descriptionRequerant;
    }

    public function setDescriptionRequerant(?string $descriptionRequerant): self
    {
        $this->descriptionRequerant = $descriptionRequerant;

        return $this;
    }

    public function getTypeAttestation(): ?TypeAttestation
    {
        return $this->typeAttestation;
    }

    public function recalculerMetaDonnees(): void
    {
        $this->typeAttestation = array_reduce(
            array_filter(
                array_map(
                    fn (Document $document) => $document->getMetaDonneesAttestation()?->typeAttestation,
                    $this->getDocumentsParType(DocumentType::TYPE_ATTESTATION_INFORMATION)
                ),
                fn (?TypeAttestation $typeAttestation) => null !== $typeAttestation
            ),
            fn (?TypeAttestation $cumul, TypeAttestation $typeAttestation) => $typeAttestation->getPrioritaire($cumul)
        );

        $typeInstitutionSecuritePublique = current(array_filter(
            array_map(
                fn (Document $document) => $document->getMetaDonneesAttestation()?->typeInstitutionSecuritePublique,
                $this->getDocumentsParType(DocumentType::TYPE_ATTESTATION_INFORMATION)
            ),
            fn (?TypeInstitutionSecuritePublique $typeInstitutionSecuritePublique) => null !== $typeInstitutionSecuritePublique
        )) ?? null;

        if (false !== $typeInstitutionSecuritePublique) {
            $this->typeInstitutionSecuritePublique = $typeInstitutionSecuritePublique;
        }
    }

    public function getOrCreateDocument(DocumentType $type): Document
    {
        /** @var null|Document $document */
        $document = null;
        if ($type->estUnique()) {
            $document = $this->getDocumentParType($type);
        }

        return $document ?? (new Document())->setType($type)->ajouterAuDossier($this);
    }

    protected function getDateEtat(EtatDossierType $etat): ?\DateTimeInterface
    {
        return $this->historiqueEtats
            ->findFirst(
                fn (int $index, EtatDossier $e) => $etat === $e->getEtat()
            )?->getDate()
        ;
    }
}
