<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\DocumentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
#[ApiResource]
class Document
{
    const TYPE_ATTESTATION_INFORMATION = "attestation_information";
    const TYPE_PHOTO_PREJUDICE = "photo_prejudice";
    const TYPE_CARTE_IDENTITE = "carte_identite";
    const TYPE_FACTURE = "facture";
    const TYPE_PREUVE_PAIEMENT_FACTURE="preuve_paiement_facture";
    const TYPE_RIB = "rib";
    const TYPE_TITRE_PROPRIETE="titre_propriete";

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $filename = null;

    #[ORM\Column(length: 40)]
    private ?string $type = null;

    #[ORM\ManyToOne(inversedBy: 'documents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?LiasseDocumentaire $liasseDocumentaire = null;

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
}
