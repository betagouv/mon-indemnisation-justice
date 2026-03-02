<?php

namespace MonIndemnisationJustice\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use MonIndemnisationJustice\Repository\BrisPorteRepository;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: BrisPorteRepository::class)]
#[ORM\Table(name: 'bris_porte')]
class BrisPorte
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    protected ?Uuid $id = null;

    #[ORM\OneToOne(targetEntity: Dossier::class, mappedBy: 'brisPorte')]
    protected Dossier $dossier;

    #[ORM\OneToOne(targetEntity: TestEligibilite::class, inversedBy: 'dossier', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    protected ?TestEligibilite $testEligibilite = null;

    #[ORM\OneToOne(targetEntity: DeclarationFDOBrisPorte::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'declaration_id', nullable: true, onDelete: 'SET NULL')]
    protected ?DeclarationFDOBrisPorte $declarationFDO = null;

    #[ORM\Column(type: 'string', length: 16, nullable: true, enumType: RapportAuLogement::class)]
    protected ?RapportAuLogement $rapportAuLogement = null;
    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $precisionRapportAuLogement = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $descriptionRequerant;

    #[ORM\Column(length: 2, nullable: true, enumType: TypeInstitutionSecuritePublique::class)]
    protected ?TypeInstitutionSecuritePublique $typeInstitutionSecuritePublique = null;

    #[ORM\Column(length: 20, nullable: true, enumType: TypeAttestation::class)]
    protected ?TypeAttestation $typeAttestation = null;

    #[ORM\ManyToOne(cascade: ['persist', 'remove'], inversedBy: 'brisPortes')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    protected ?Adresse $adresse;

    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    protected ?\DateTimeInterface $dateOperation = null;


    #[ORM\Column(options: ['default' => false])]
    protected bool $estPorteBlindee = false;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getTestEligibilite(): ?TestEligibilite
    {
        return $this->testEligibilite;
    }

    public function setTestEligibilite(?TestEligibilite $testEligibilite): self
    {
        $this->testEligibilite = $testEligibilite;

        return $this;
    }

    public function getDeclarationFDO(): ?DeclarationFDOBrisPorte
    {
        return $this->declarationFDO;
    }

    public function setDeclarationFDO(?DeclarationFDOBrisPorte $declarationFDO): self
    {
        $this->declarationFDO = $declarationFDO;

        return $this;
    }

    public function getRapportAulogement(): ?RapportAuLogement
    {
        return $this->rapportAulogement;
    }

    public function setRapportAulogement(?RapportAuLogement $rapportAulogement): self
    {
        $this->rapportAulogement = $rapportAulogement;

        return $this;
    }

    public function getPrecisionRapportAuLogement(): ?string
    {
        return $this->precisionRapportAuLogement;
    }

    public function setPrecisionRapportAuLogement(?string $precisionRapportAuLogement): self
    {
        $this->precisionRapportAuLogement = $precisionRapportAuLogement;

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

    public function getTypeInstitutionSecuritePublique(): ?TypeInstitutionSecuritePublique
    {
        return $this->typeInstitutionSecuritePublique;
    }

    public function setTypeInstitutionSecuritePublique(?TypeInstitutionSecuritePublique $typeInstitutionSecuritePublique): self
    {
        $this->typeInstitutionSecuritePublique = $typeInstitutionSecuritePublique;

        return $this;
    }

    public function getTypeAttestation(): ?TypeAttestation
    {
        return $this->typeAttestation;
    }

    public function setTypeAttestation(?TypeAttestation $typeAttestation): self
    {
        $this->typeAttestation = $typeAttestation;

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

    public function getDateOperation(): ?\DateTimeInterface
    {
        return $this->dateOperation;
    }

    public function setDateOperation(?\DateTimeInterface $dateOperation): self
    {
        $this->dateOperation = $dateOperation;

        return $this;
    }

    public function estPorteBlindee(): bool
    {
        return $this->estPorteBlindee;
    }

    public function setPorteBlindee(bool $estPorteBlindee): self
    {
        $this->estPorteBlindee = $estPorteBlindee;

        return $this;
    }
}
