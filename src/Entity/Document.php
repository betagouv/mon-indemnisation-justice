<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\DocumentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
#[ORM\Index(name: "document_liasse_documentaire_id_type_idx", columns: ["liasse_documentaire_id","type"])]
#[ApiResource(
  operations:[
    new Get(
        name: '_api_document_get',
        security: "is_granted('ROLE_REQUERANT')"
    ),
    new GetCollection(
        name: '_api_document_get_collection',
        security: "is_granted('ROLE_REQUERANT')"
    ),
  ]
)]
class Document
{
    const TYPE_ATTESTATION_INFORMATION = "attestation_information";
    const TYPE_PHOTO_PREJUDICE = "photo_prejudice";
    const TYPE_CARTE_IDENTITE = "carte_identite";
    const TYPE_FACTURE = "facture";
    const TYPE_PREUVE_PAIEMENT_FACTURE="preuve_paiement_facture";
    const TYPE_RIB = "rib";
    const TYPE_TITRE_PROPRIETE="titre_propriete";
    const TYPE_CONTRAT_LOCATION="contrat_location";
    const TYPE_SIGNATURE_DECISION="signature_decision";

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?string $filename = null;

    #[ApiFilter(SearchFilter::class, strategy: 'exact')]
    #[ORM\Column(length: 40)]
    private ?string $type = null;

    #[ORM\Column(length: 64, nullable: true)]
    protected ?string $mime = null;

    #[ApiFilter(SearchFilter::class, strategy: 'exact')]
    #[ORM\ManyToOne(inversedBy: 'documents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?LiasseDocumentaire $liasseDocumentaire = null;

    #[ORM\Column(nullable: true)]
    private ?string $size = null;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $originalFilename = null;

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

    public function getLiasseDocumentaire(): ?LiasseDocumentaire
    {
        return $this->liasseDocumentaire;
    }

    public function setLiasseDocumentaire(?LiasseDocumentaire $liasseDocumentaire): static
    {
        $this->liasseDocumentaire = $liasseDocumentaire;

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
