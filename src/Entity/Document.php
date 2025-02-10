<?php

namespace MonIndemnisationJustice\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use MonIndemnisationJustice\Repository\DocumentRepository;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
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
    public const TYPE_SIGNATURE_DECISION = 'signature_decision';

    public static $types = [
        self::TYPE_ATTESTATION_INFORMATION => "Attestation à remettre en cas d'erreur de porte", // Dossier
        self::TYPE_PHOTO_PREJUDICE => 'Photo de la porte endommagée', // Dossier
        self::TYPE_CARTE_IDENTITE => "Pièce d'identité", // Personne morale OU physique
        self::TYPE_FACTURE => 'Facture', // Dossier
        self::TYPE_RIB => 'RIB', // Personne morale OU physique
        self::TYPE_TITRE_PROPRIETE => 'Titre de propriété', // Dossier
        self::TYPE_CONTRAT_LOCATION => 'Contrat de location', // Dossier
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    #[Groups(['dossier:lecture'])]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['dossier:lecture'])]
    private ?string $filename = null;

    #[Groups(['dossier:lecture'])]
    #[ORM\Column(length: 40)]
    private ?string $type = null;

    #[Groups(['dossier:lecture'])]
    #[ORM\Column(length: 64, nullable: true)]
    protected ?string $mime = null;

    #[Groups(['dossier:lecture'])]
    #[ORM\Column(nullable: true)]
    private ?string $size = null;

    #[Groups(['dossier:lecture'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $originalFilename = null;

    #[ORM\ManyToMany(targetEntity: BrisPorte::class, mappedBy: 'documents')]
    /** @var Collection<BrisPorte> */
    protected Collection $dossiers;

    public function __construct()
    {
        $this->dossiers = new ArrayCollection();
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

    public function getContentId(): string
    {
        return "$this->type+$this->filename";
    }
}
