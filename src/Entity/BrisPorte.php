<?php

namespace MonIndemnisationJustice\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Patch;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Mapping as ORM;
use MonIndemnisationJustice\Repository\BrisPorteRepository;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Attribute\Context;
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
class BrisPorte
{
    #[Groups('dossier:lecture')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    public ?int $id = null;

    #[Groups(['dossier:lecture', 'dossier:patch'])]
    #[ORM\ManyToOne(targetEntity: Requerant::class, cascade: ['persist', 'remove'], inversedBy: 'dossiers')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    protected Requerant $requerant;

    #[ORM\ManyToOne(targetEntity: Agent::class, cascade: ['persist', 'remove'], inversedBy: 'dossiers')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    protected ?Agent $redacteur = null;

    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $notes = null;

    #[ORM\OneToOne(targetEntity: EtatDossier::class, inversedBy: null)]
    #[ORM\JoinColumn(name: 'etat_actuel_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected ?EtatDossier $etatDossier = null;

    #[ORM\OneToMany(targetEntity: EtatDossier::class, mappedBy: 'dossier', cascade: ['persist', 'remove'], fetch: 'EAGER')]
    #[ORM\OrderBy(['dateEntree' => 'ASC'])]
    /** @var Collection<EtatDossier> */
    protected Collection $historiqueEtats;

    #[ORM\OneToOne(targetEntity: CourrierDossier::class, inversedBy: null, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'courrier_actuel_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected ?CourrierDossier $courrier = null;

    #[ORM\OneToMany(targetEntity: CourrierDossier::class, mappedBy: 'dossier', cascade: ['persist', 'remove'], fetch: 'LAZY')]
    #[ORM\OrderBy(['dateCreation' => 'ASC'])]
    /** @var Collection<CourrierDossier> */
    protected Collection $historiqueCourriers;

    #[Groups('dossier:lecture')]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    protected \DateTimeInterface $dateCreation;

    #[Groups('dossier:lecture')]
    #[ORM\Column(length: 20, nullable: true)]
    private ?string $reference = null;

    #[ORM\JoinTable(name: 'document_dossiers')]
    #[ORM\JoinColumn(name: 'dossier_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToMany(targetEntity: Document::class, inversedBy: 'dossiers', cascade: ['persist'])]
    /** @var Collection<Document> */
    protected Collection $documents;
    protected ?array $documentsParType = null;

    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $corpsCourrier = null;

    #[Groups('dossier:patch')]
    #[ORM\Column(type: Types::FLOAT, precision: 10, scale: 2, nullable: true)]
    private ?float $propositionIndemnisation = null;

    #[Groups('dossier:lecture')]
    #[ORM\Column(length: 20, nullable: true)]
    /**
     * Numéro de référence raccourci, pour la recherche en ligne (comparable au numéro de réservation chez une
     * compagnie aérienne).
     */
    private ?string $raccourci = null;

    #[Groups(['dossier:lecture', 'dossier:patch'])]
    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $numeroPV = null;

    #[Groups(['dossier:lecture', 'dossier:patch'])]
    #[ORM\ManyToOne(inversedBy: 'brisPortes', cascade: ['persist'])]
    private ?Adresse $adresse;

    #[Groups(['dossier:lecture', 'dossier:patch'])]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateOperationPJ = null;

    #[Groups(['dossier:lecture', 'dossier:patch'])]
    #[ORM\Column(options: ['default' => false])]
    private bool $isPorteBlindee = false;

    #[ORM\OneToOne(targetEntity: TestEligibilite::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    protected ?TestEligibilite $testEligibilite = null;

    #[Groups(['dossier:lecture', 'dossier:patch'])]
    #[ORM\Column(type: 'string', length: 3, nullable: true, enumType: QualiteRequerant::class)]
    protected ?QualiteRequerant $qualiteRequerant = null;

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
        $this->changerStatut(EtatDossierType::DOSSIER_A_FINALISER, requerant: true);
    }

    #[ORM\PreRemove]
    public function onPreRemove(): void
    {
        $this->etatDossier = null;
    }

    #[ORM\PostLoad]
    public function onLoaded(): void
    {
        $this->documentsParType = $this->documents->reduce(
            function (array $carry, Document $document) {
                if (!isset($carry[$document->getType()])) {
                    $carry[$document->getType()] = [];
                }

                $carry[$document->getType()][] = $document;

                return $carry;
            }, array_merge(
                ...array_map(
                    fn (string $type) => [$type => []],
                    array_keys(Document::$types)
                )
            )
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

    public function getRequerant(): ?Requerant
    {
        return $this->requerant;
    }

    public function setRequerant(?Requerant $requerant): self
    {
        $this->requerant = $requerant;

        return $this;
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

        return $this;
    }

    public function getLastStatut(): EtatDossier
    {
        return $this->getEtatDossier();
    }

    public function getEtatDossier(): ?EtatDossier
    {
        return $this->etatDossier;
    }

    public function estASigner(): bool
    {
        return $this->etatDossier->estASigner();
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

    public function estConstitue(): bool
    {
        return null !== $this->getDateDeclaration();
    }

    public function getDateCreation(): \DateTimeInterface
    {
        return $this->dateCreation;
    }

    #[Groups('dossier:lecture')]
    public function getDateDeclaration(): ?\DateTimeInterface
    {
        return $this->historiqueEtats
            ->findFirst(fn (int $index, EtatDossier $etat) => EtatDossierType::DOSSIER_A_INSTRUIRE === $etat->getEtat()
            )?->getDate();
    }

    public function setDeclare(): self
    {
        return $this
            ->changerStatut(EtatDossierType::DOSSIER_A_INSTRUIRE, requerant: true);
    }

    public function getCourrier(): ?CourrierDossier
    {
        return $this->courrier;
    }

    public function setCourrier(?CourrierDossier $courrier): BrisPorte
    {
        $this->courrier = $courrier;

        return $this;
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

        $this->documentsParType[$document->getType()][] = $document;
    }

    /**
     * @return Document[]|null
     */
    #[Groups('dossier:lecture')]
    public function getDocuments(): array
    {
        return $this->documentsParType;
    }

    public function getDocumentsParType(string $type): array
    {
        return $this->documentsParType[$type] ?? [];
    }

    public function getPropositionIndemnisation(): ?string
    {
        return $this->propositionIndemnisation;
    }

    public function setPropositionIndemnisation(?string $propositionIndemnisation): self
    {
        $this->propositionIndemnisation = $propositionIndemnisation;

        return $this;
    }

    public function getCorpsCourrier(): ?string
    {
        return $this->corpsCourrier;
    }

    public function setCorpsCourrier(?string $corpsCourrier): BrisPorte
    {
        $this->corpsCourrier = $corpsCourrier;

        return $this;
    }

    public function getRaccourci(): ?string
    {
        return $this->raccourci;
    }

    public function setRaccourci(?string $raccourci): self
    {
        $this->raccourci = $raccourci;

        return $this;
    }

    public function getType(): PrejudiceType
    {
        return PrejudiceType::BRIS_PORTE;
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

    public function setDateOperationPJ(?\DateTimeInterface $dateOperationPJ): self
    {
        $this->dateOperationPJ = $dateOperationPJ;

        return $this;
    }

    public function getTestEligibilite(): ?TestEligibilite
    {
        return $this->testEligibilite;
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
}
