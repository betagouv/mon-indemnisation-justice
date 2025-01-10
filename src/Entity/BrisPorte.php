<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Patch;
use App\Repository\BrisPorteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Mapping as ORM;
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

    #[ORM\OneToMany(targetEntity: EtatDossier::class, mappedBy: 'dossier', cascade: ['persist', 'remove'], fetch: 'EAGER')]
    #[ORM\OrderBy(['dateEntree' => 'ASC'])]
    /** @var Collection<EtatDossier> */
    protected Collection $historiqueEtats;

    #[ORM\OneToOne(targetEntity: EtatDossier::class, inversedBy: null)]
    #[ORM\JoinColumn(name: 'etat_actuel_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected ?EtatDossier $etatDossier = null;

    #[Groups('dossier:lecture')]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    protected \DateTimeInterface $dateCreation;

    #[Groups('dossier:lecture')]
    #[ORM\Column(length: 20, nullable: true)]
    private ?string $reference = null;

    #[Groups('dossier:lecture')]
    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?LiasseDocumentaire $liasseDocumentaire = null;

    /** @var Document[]|null */
    protected ?array $documents = null;

    #[Groups('dossier:patch')]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $propositionIndemnisation = null;

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

    #[Groups(['dossier:lecture', 'dossier:patch'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numeroParquet = null;

    public function __construct()
    {
        $this->dateCreation = new \DateTimeImmutable();
        $this->liasseDocumentaire = new LiasseDocumentaire();
        $this->adresse = new Adresse();
        $this->historiqueEtats = new ArrayCollection([]);
    }

    #[ORM\PrePersist]
    public function onPrePersist(PrePersistEventArgs $args): void
    {
        $this->changerStatut(EtatDossierType::DOSSIER_INITIE, requerant: true);
    }

    #[ORM\PreRemove]
    public function onPreRemove(): void
    {
        $this->etatDossier = null;
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

    public function changerStatut(EtatDossierType $type, bool $requerant = false, ?Agent $agent = null): self
    {
        if ($requerant) {
            $this->historiqueEtats->add(EtatDossier::creerRequerant($this, $type));
        } elseif (null !== $agent) {
            $this->historiqueEtats->add(EtatDossier::creerAgent($this, $type, $agent));
        } else {
            $this->historiqueEtats->add(EtatDossier::creer($this, $type));
        }

        $this->etatDossier = $this->historiqueEtats->last();

        return $this;
    }

    #[Groups(['prejudice:read'])]
    public function getLastStatut(): EtatDossier
    {
        return $this->getEtatDossier();
    }

    public function getEtatDossier(): ?EtatDossier
    {
        return $this->etatDossier;
    }

    public function getEtatPreValide(): ?EtatDossier
    {
        return $this->getEtat(EtatDossierType::DOSSIER_PRE_VALIDE) ?? $this->getEtat(EtatDossierType::DOSSIER_PRE_REFUSE);
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

    public function getDateDeclaration(): ?\DateTimeInterface
    {
        return $this->historiqueEtats
            ->findFirst(fn (int $index, EtatDossier $etat) => EtatDossierType::DOSSIER_DEPOSE === $etat->getEtat()
            )?->getDate();
    }

    public function setDeclare(): self
    {
        return $this
            ->changerStatut(EtatDossierType::DOSSIER_DEPOSE, requerant: true);
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

    public function getLiasseDocumentaire(): ?LiasseDocumentaire
    {
        return $this->liasseDocumentaire;
    }

    public function setLiasseDocumentaire(LiasseDocumentaire $liasseDocumentaire): self
    {
        $this->liasseDocumentaire = $liasseDocumentaire;

        return $this;
    }

    /**
     * @return Document[]|null
     */
    public function getDocuments(string $type): ?array
    {
        if (null === $this->documents) {
            $this->documents = [];

            foreach (Document::$types as $type => $libelle) {
                if (in_array($type, [Document::TYPE_CARTE_IDENTITE, Document::TYPE_RIB])) {
                    if ($this->requerant->getIsPersonneMorale()) {
                        $this->documents[$type] = $this->requerant->getPersonneMorale()->getLiasseDocumentaire()?->getDocuments()->filter(fn (Document $document) => $type === $document->getType())->toArray();
                    } else {
                        $this->documents[$type] = $this->requerant->getPersonnePhysique()->getLiasseDocumentaire()?->getDocuments()->filter(fn (Document $document) => $type === $document->getType())->toArray();
                    }
                } else {
                    $this->documents[$type] = $this->liasseDocumentaire?->getDocuments()->filter(fn (Document $document) => $type === $document->getType())->toArray();
                }
            }
        }

        return $this->documents[$type] ?? null;
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


    public function getNumeroParquet(): ?string
    {
        return $this->numeroParquet;
    }

    public function setNumeroParquet(?string $numeroParquet): self
    {
        $this->numeroParquet = $numeroParquet;

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

    public function getReceveurAttestation(): ?PersonnePhysique
    {
        return $this->receveurAttestation;
    }

    public function setReceveurAttestation(?PersonnePhysique $receveurAttestation): self
    {
        $this->receveurAttestation = $receveurAttestation;

        return $this;
    }
}
