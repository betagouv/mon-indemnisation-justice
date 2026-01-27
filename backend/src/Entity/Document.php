<?php

namespace MonIndemnisationJustice\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use MonIndemnisationJustice\Entity\Metadonnees\MetadonneesAttestation;
use MonIndemnisationJustice\Repository\DocumentRepository;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[\AllowDynamicProperties]
class Document
{
    #[ORM\Column(nullable: true)]
    public ?bool $estValide = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    public ?\DateTimeInterface $dateValidation = null;

    #[ORM\ManyToOne(targetEntity: Agent::class, cascade: [])]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    public ?Agent $validateur = null;
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    #[Groups(['dossier:lecture', 'agent:detail', 'requerant:detail'])]
    protected ?int $id = null;

    #[Groups(['dossier:lecture', 'agent:detail', 'requerant:detail'])]
    #[ORM\Column(nullable: true)]
    protected ?string $mime = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    protected \DateTimeInterface $dateAjout;

    /**
     * Si `true`, alors ajouté par le requérant lors du dépôt de dossier.
     * Si `false`, alors téléversé en complément ou édité par l'agent.
     *
     * Sinon automatique, ex: arrêté de paiement post acceptation.
     * */
    #[ORM\Column(nullable: true)]
    #[Groups(['agent:detail'])]
    protected ?bool $estAjoutRequerant = null;

    #[Groups(['agent:detail'])]
    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $corps = null;

    #[Groups(['agent:detail'])]
    #[ORM\Column(type: 'json', nullable: true)]
    protected ?array $metaDonnees = null;

    #[ORM\ManyToMany(targetEntity: BrisPorte::class, mappedBy: 'documents', cascade: ['persist'])]
    #[Ignore]
    /** @var Collection<BrisPorte> */
    protected Collection $dossiers;

    #[Groups(['agent:detail'])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    protected ?\DateTimeInterface $dateDerniereModification = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['dossier:lecture', 'requerant:detail'])]
    private ?string $filename = null;

    #[Groups(['dossier:lecture', 'agent:detail', 'requerant:detail'])]
    #[ORM\Column(length: 40, enumType: DocumentType::class)]
    private DocumentType $type;

    #[Groups(['dossier:lecture', 'agent:detail'])]
    #[ORM\Column(nullable: true)]
    private ?int $size = null;

    #[Groups(['dossier:lecture', 'requerant:detail', 'agent:detail'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $originalFilename = null;

    public function __construct()
    {
        $this->dossiers = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function onPersist(PrePersistEventArgs $args): void
    {
        $this->dateAjout = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onUpdate(PreUpdateEventArgs $args): void
    {
        $this->setModifie();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Document
    {
        return $this;
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

    public function getType(): DocumentType
    {
        return $this->type;
    }

    public function setType(DocumentType $type): static
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

    public function getDateAjout(): \DateTimeInterface
    {
        return $this->dateAjout;
    }

    public function estAjoutRequerant(): ?bool
    {
        return $this->estAjoutRequerant;
    }

    public function setAjoutRequerant(?bool $estAjoutRequerant): static
    {
        $this->estAjoutRequerant = $estAjoutRequerant;

        return $this;
    }

    public function ajouterAuDossier(BrisPorte $dossier): static
    {
        $dossier->ajouterDocument($this);
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

    #[Ignore]
    public function getDossier(): ?BrisPorte
    {
        return $this->dossiers->first();
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(?int $size): static
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

    public function getCorps(): ?string
    {
        return $this->corps;
    }

    public function setCorps(?string $corps): static
    {
        $this->corps = $corps;

        return $this;
    }

    public function estEditable(): bool
    {
        return null !== $this->type->getGabarit();
    }

    public function valider(Agent $agent): self
    {
        return $this->setValidation(true, $agent);
    }

    public function rejeter(Agent $agent): self
    {
        return $this->setValidation(false, $agent);
    }

    #[Groups(['agent:detail', 'requerant:detail'])]
    public function getFileHash(): string
    {
        return md5($this->filename);
    }

    public function getMetaDonnee(string $key): mixed
    {
        if (is_array($this->metaDonnees)) {
            return @$this->metaDonnees[$key] ?? null;
        }

        return null;
    }

    public function getMetaDonnees(): ?array
    {
        return $this->metaDonnees;
    }

    public function getMetaDonneesAttestation(): ?MetadonneesAttestation
    {
        return new MetadonneesAttestation(
            typeAttestation: null !== ($typeAttestation = $this->getMetaDonnee('typeAttestation')) ? TypeAttestation::tryFrom($typeAttestation) : null,
            typeInstitutionSecuritePublique: null !== ($typeInstitutionSecuritePublique = $this->getMetaDonnee('typeInstitutionSecuritePublique')) ? TypeInstitutionSecuritePublique::tryFrom($typeInstitutionSecuritePublique) : null
        );
    }

    public function setMetaDonneesAttestation(MetadonneesAttestation $metaDonnees, bool $merge = false): static
    {
        return $this->setMetaDonnees([
            'typeAttestation' => $metaDonnees->typeAttestation?->value ?? null,
            'typeInstitutionSecuritePublique' => $metaDonnees->typeInstitutionSecuritePublique?->value ?? null,
        ], $merge);
    }

    public function setMetaDonnees(array $metaDonnees, bool $merge = false): static
    {
        $this->metaDonnees = $merge ? array_merge($this->metaDonnees ?? [], $metaDonnees) : $metaDonnees;

        return $this;
    }

    public function getDateDerniereModification(): ?\DateTimeInterface
    {
        return $this->dateDerniereModification;
    }

    public function setDateDerniereModification(?\DateTimeInterface $dateDerniereModification): Document
    {
        $this->dateDerniereModification = $dateDerniereModification;

        return $this;
    }

    public function setModifie(): static
    {
        $this->dateDerniereModification = new \DateTimeImmutable();

        return $this;
    }

    protected function setValidation(bool $estValide, Agent $agent): self
    {
        $this->estValide = $estValide;
        $this->dateValidation = new \DateTimeImmutable();
        $this->validateur = $agent;

        return $this;
    }
}
