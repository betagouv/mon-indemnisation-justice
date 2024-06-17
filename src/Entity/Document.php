<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\DocumentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
#[ApiResource(
  operations:[
    new Get(name: '_api_document_get'),
    new GetCollection(name: '_api_document_get_collection'),
  ]
)]
#[Vich\Uploadable]
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

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?string $filename = null;

    #[ApiFilter(SearchFilter::class, strategy: 'exact')]
    #[ORM\Column(length: 40)]
    private ?string $type = null;

    #[Vich\UploadableField(mapping: 'document', fileNameProperty: 'filename', size: 'size')]
    private ?File $file = null;

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

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file = null): void
    {
        $this->file = $file;

        if (null !== $file)
            $this->date = new \DateTimeImmutable();
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
}
