<?php

namespace MonIndemnisationJustice\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Mapping as ORM;
use MonIndemnisationJustice\Repository\DocumentRepository;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Document
{
    public const TYPE_ATTESTATION_INFORMATION = 'attestation_information';
    public const TYPE_PHOTO_PREJUDICE = 'photo_prejudice';
    public const TYPE_CARTE_IDENTITE = 'carte_identite';
    public const TYPE_FACTURE = 'facture';
    public const TYPE_PREUVE_PAIEMENT_FACTURE = 'preuve_paiement_facture';
    public const TYPE_RIB = 'rib';
    public const TYPE_TITRE_PROPRIETE = 'titre_propriete';
    public const TYPE_CONTRAT_LOCATION = 'contrat_location';
    public const TYPE_ATTESTATION_NON_PRISE_EN_CHARGE_BAILLEUR = 'non_prise_en_charge_bailleur';

    public const TYPE_ATTESTATION_NON_PRISE_EN_CHARGE_ASSURANCE = 'non_prise_en_charge_assurance';

    public const TYPE_COURRIER_MINISTERE = 'courrier_ministere';

    public const TYPE_COURRIER_REQUERANT = 'courrier_requerant';

    public const TYPE_ARRETE_PAIEMENT = 'arrete_paiement';

    public static $types = [
        self::TYPE_ATTESTATION_INFORMATION => "Attestation à remettre en cas d'erreur de porte", // Dossier
        self::TYPE_PHOTO_PREJUDICE => 'Photo de la porte endommagée', // Dossier
        self::TYPE_CARTE_IDENTITE => "Pièce d'identité", // Personne morale OU physique
        self::TYPE_FACTURE => 'Facture', // Dossier
        self::TYPE_RIB => 'RIB', // Personne morale OU physique
        self::TYPE_TITRE_PROPRIETE => 'Titre de propriété', // Dossier
        self::TYPE_CONTRAT_LOCATION => 'Contrat de location', // Dossier
        self::TYPE_ATTESTATION_NON_PRISE_EN_CHARGE_BAILLEUR => 'Attestation de non prise en charge par le bailleur',
        self::TYPE_ATTESTATION_NON_PRISE_EN_CHARGE_ASSURANCE => "Attestation de non prise en charge par l'assurance habitation",
        self::TYPE_COURRIER_MINISTERE => 'Courrier signé de décision du Ministere',
        self::TYPE_COURRIER_REQUERANT => "Courrier signé d'acceptation du requérant",
        self::TYPE_ARRETE_PAIEMENT => 'Arrêté de paiement',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    #[Groups(['dossier:lecture', 'agent:detail', 'requerant:detail'])]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['dossier:lecture'])]
    private ?string $filename = null;

    #[Groups(['dossier:lecture', 'agent:detail', 'requerant:detail'])]
    #[ORM\Column(length: 40)]
    private ?string $type = null;

    #[Groups(['dossier:lecture', 'agent:detail', 'requerant:detail'])]
    #[ORM\Column(nullable: true)]
    protected ?string $mime = null;

    #[Groups(['dossier:lecture'])]
    #[ORM\Column(nullable: true)]
    private ?string $size = null;

    #[Groups(['dossier:lecture', 'agent:detail', 'requerant:detail'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $originalFilename = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    protected \DateTimeInterface $dateAjout;

    /**
     * Si `true`, alors ajouté par le requérant lors du dépôt de dossier.
     * Si `false`, alors téléversé en complément ou édité par l'agent.
     *
     * Sinon automatique, ex: arrêté de paiement post acceptation.
     * */
    #[ORM\Column(nullable: true)]
    protected ?bool $estAjoutRequerant = null;

    #[ORM\Column(nullable: true)]
    protected ?bool $estValide = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    protected ?\DateTimeInterface $dateValidation = null;

    #[ORM\ManyToOne(targetEntity: Agent::class, cascade: [])]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    protected ?Agent $validateur;

    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $corpsCourrier = null;

    #[Groups(['agent:detail'])]
    #[ORM\Column(type: 'json', nullable: true)]
    protected ?array $metaDonnees = null;

    #[ORM\ManyToMany(targetEntity: BrisPorte::class, mappedBy: 'documents')]
    /** @var Collection<BrisPorte> */
    protected Collection $dossiers;

    public function __construct()
    {
        $this->dossiers = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function onPersist(PrePersistEventArgs $args): void
    {
        $this->dateAjout = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getTypeLibelle(): ?string
    {
        return self::$types[$this->type] ?? null;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getMime(): ?string
    {
        return $this->mime;
    }

    public function setMime(?string $mime): Document
    {
        $this->mime = $mime;

        return $this;
    }

    public function ajouterAuDossier(BrisPorte $dossier): static
    {
        $this->dossiers->add($dossier);

        return $this;
    }

    /**
     * @return Collection<BrisPorte>
     */
    public function getDossiers(): Collection
    {
        return $this->dossiers;
    }

    public function getSize(): ?string
    {
        return $this->size;
    }

    public function setSize(?string $size): static
    {
        $this->size = $size;

        return $this;
    }

    public function getOriginalFilename(): ?string
    {
        return $this->originalFilename;
    }

    public function setOriginalFilename(string $originalFilename): static
    {
        $this->originalFilename = $originalFilename;

        return $this;
    }

    public function valider(Agent $agent): self
    {
        return $this->setValidation(true, $agent);
    }

    public function rejeter(Agent $agent): self
    {
        return $this->setValidation(false, $agent);
    }

    protected function setValidation(bool $estValide, Agent $agent): self
    {
        $this->estValide = $estValide;
        $this->dateValidation = new \DateTimeImmutable();
        $this->validateur = $agent;

        return $this;
    }

    public function getFileHash(): string
    {
        return md5($this->filename);
    }

    public function getMetaDonnee(string $key): mixed
    {
        if (is_array($this->metaDonnees)) {
            return @$this->metaDonnees[$key];
        }

        return null;
    }

    public function getMetaDonnees(): ?array
    {
        return $this->metaDonnees;
    }

    public function setMetaDonnees(array $metaDonnees, bool $merge = false): static
    {
        $this->metaDonnees = $merge ? array_merge($this->metaDonnees ?? [], $metaDonnees) : $metaDonnees;

        return $this;
    }

    #[Groups(['agent:detail'])]
    #[SerializedName('url')]
    public function getAgentUrl(): ?string
    {
        // URL pointant sur la route "agent_document_download"
        return "/agent/document/$this->id/{$this->getFileHash()}";
    }

    #[Groups(['requerant:detail'])]
    #[SerializedName('url')]
    public function getRequerantUrl(): ?string
    {
        // URL pointant sur la route "agent_document_download"
        return "/requerant/document/$this->id/{$this->filename}";
    }
}
