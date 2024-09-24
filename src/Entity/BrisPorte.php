<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use App\Controller\Requerant\GetBrisPorteOptimized;
use App\Repository\BrisPorteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Get(
            normalizationContext: ['groups' => ['prejudice:read']],
            name: '_api_bris_porte_get'
        ),
        new Get(
            uriTemplate: '/requerant/bris-de-porte/{id}/optimise',
            controller: GetBrisPorteOptimized::class,
            name: '_api_bris_porte_get_optimized'
        ),
        new GetCollection(),
        new Patch(
            normalizationContext: ['groups' => ['prejudice:write']],
            name: '_api_bris_porte_patch'
        ),
    ]
)]
#[ORM\Entity(repositoryClass: BrisPorteRepository::class)]
#[ORM\Table(name: 'bris_porte')]
class BrisPorte
{
    #[Groups('prejudice:read')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    public ?int $id = null;

    #[Groups('prejudice:read')]
    #[ORM\ManyToOne(targetEntity: Requerant::class, cascade: ['persist', 'remove'], inversedBy: 'brisPorte')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    protected Requerant $requerant;

    #[Groups('prejudice:read')]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    protected \DateTimeInterface $dateCreation;

    #[Groups('prejudice:read')]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?\DateTimeInterface $dateDeclaration = null;

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

    #[Groups(['prejudice:read', 'prejudice:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $numeroPV = null;

    #[Groups(['prejudice:read', 'prejudice:write'])]
    #[ORM\ManyToOne(inversedBy: 'brisPortes', cascade: ['persist'])]
    private ?Adresse $adresse = null;

    #[Groups(['prejudice:read', 'prejudice:write'])]
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateOperationPJ = null;

    #[Groups(['prejudice:read', 'prejudice:write'])]
    #[ORM\Column(options: ['default' => false])]
    private bool $isPorteBlindee = false;

    #[Groups(['prejudice:read', 'prejudice:write'])]
    #[ORM\Column(options: ['default' => false])]
    private bool $isErreurPorte = false;

    #[Groups(['prejudice:read', 'prejudice:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $identitePersonneRecherchee = null;

    #[Groups(['prejudice:read', 'prejudice:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomRemiseAttestation = null;

    #[Groups(['prejudice:read', 'prejudice:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prenomRemiseAttestation = null;

    #[Groups(['prejudice:read', 'prejudice:write'])]
    #[ORM\Column(type: 'string', length: 3, nullable: true, enumType: QualiteRequerant::class)]
    protected ?QualiteRequerant $qualiteRequerant = null;

    #[Groups(['prejudice:read', 'prejudice:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $precisionRequerant = null;

    #[Groups(['prejudice:read', 'prejudice:write'])]
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateAttestationInformation = null;

    #[Groups(['prejudice:read', 'prejudice:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numeroParquet = null;

    #[Groups(['prejudice:read', 'prejudice:write'])]
    #[ORM\ManyToOne(cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?PersonnePhysique $receveurAttestation = null;

    #[Groups(['prejudice:read', 'prejudice:write'])]
    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?ServiceEnqueteur $serviceEnqueteur = null;

    public function __construct()
    {
        $this->dateCreation = new \DateTimeImmutable();
        $this->liasseDocumentaire = new LiasseDocumentaire();
        $this->receveurAttestation = new PersonnePhysique();
        $this->adresse = new Adresse();
        $this->serviceEnqueteur = new ServiceEnqueteur();
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

    public function getRequerant(): ?Requerant
    {
        return $this->requerant;
    }

    public function setRequerant(?Requerant $requerant): self
    {
        $this->requerant = $requerant;

        return $this;
    }

    #[Groups(['prejudice:read'])]
    public function getLastStatut(): BrisPorteStatut
    {
        return null !== $this->dateDeclaration ? BrisPorteStatut::CONSTITUE : BrisPorteStatut::EN_COURS_DE_CONSTITUTION;
    }

    public function getDateCreation(): \DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getDateDeclaration(): ?\DateTimeInterface
    {
        return $this->dateDeclaration;
    }

    public function setDateDeclaration(\DateTimeInterface $dateDeclaration): self
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

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

        return $this;
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

    public function getMotivationProposition(): ?string
    {
        return $this->motivationProposition;
    }

    public function setMotivationProposition(?string $motivationProposition): self
    {
        $this->motivationProposition = $motivationProposition;

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

    public function isErreurPorte(): ?bool
    {
        return $this->isErreurPorte;
    }

    public function getIsErreurPorte(): ?bool
    {
        return $this->isErreurPorte();
    }

    public function setErreurPorte(?bool $isErreurPorte): self
    {
        $this->isErreurPorte = $isErreurPorte;

        return $this;
    }

    public function setIsErreurPorte(?bool $isErreurPorte): self
    {
        $this->setErreurPorte($isErreurPorte);

        return $this;
    }

    public function getIdentitePersonneRecherchee(): ?string
    {
        return $this->identitePersonneRecherchee;
    }

    public function setIdentitePersonneRecherchee(?string $identitePersonneRecherchee): self
    {
        $this->identitePersonneRecherchee = $identitePersonneRecherchee;

        return $this;
    }

    public function getNomRemiseAttestation(): ?string
    {
        return $this->nomRemiseAttestation;
    }

    public function setNomRemiseAttestation(?string $nomRemiseAttestation): self
    {
        $this->nomRemiseAttestation = $nomRemiseAttestation;

        return $this;
    }

    public function getPrenomRemiseAttestation(): ?string
    {
        return $this->prenomRemiseAttestation;
    }

    public function setPrenomRemiseAttestation(?string $prenomRemiseAttestation): self
    {
        $this->prenomRemiseAttestation = $prenomRemiseAttestation;

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

    public function getDateAttestationInformation(): ?\DateTimeInterface
    {
        return $this->dateAttestationInformation;
    }

    public function setDateAttestationInformation(\DateTimeInterface $dateAttestationInformation): self
    {
        $this->dateAttestationInformation = $dateAttestationInformation;

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

    public function getServiceEnqueteur(): ?ServiceEnqueteur
    {
        return $this->serviceEnqueteur;
    }

    public function setServiceEnqueteur(ServiceEnqueteur $serviceEnqueteur): self
    {
        $this->serviceEnqueteur = $serviceEnqueteur;

        return $this;
    }
}
