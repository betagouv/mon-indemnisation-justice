<?php

namespace MonIndemnisationJustice\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'signature_requetes_requerant')]
class RequeteSignatureRequerant
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    protected ?Uuid $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    protected string $identifiantExterieur;

    #[ORM\ManyToOne(targetEntity: Dossier::class, inversedBy: 'requetesSignatureRequerant')]
    protected Dossier $dossier;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    protected \DateTimeImmutable $dateCreation;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    protected ?\DateTimeImmutable $dateActivation = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    protected ?\DateTimeImmutable $dateSignature = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    protected ?\DateTimeImmutable $dateAnnulation = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    protected ?\DateTimeImmutable $dateExpiration = null;

    #[ORM\Column(nullable: true)]
    protected ?string $urlSignatureExterne = null;

    /**
     * Coordonnées bancaires du compte sur lequel le requérant accepte d'être indemnisé.
     */
    #[ORM\ManyToOne(targetEntity: ReferenceBancaire::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'reference_bancaire_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    protected ?ReferenceBancaire $referenceBancaire = null;

    public function __construct()
    {
        $this->dateCreation = new \DateTimeImmutable();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getIdentifiantExterieur(): string
    {
        return $this->identifiantExterieur;
    }

    public function setIdentifiantExterieur(string $identifiantExterieur): RequeteSignatureRequerant
    {
        $this->identifiantExterieur = $identifiantExterieur;

        return $this;
    }

    public function getDossier(): Dossier
    {
        return $this->dossier;
    }

    public function setDossier(Dossier $dossier): RequeteSignatureRequerant
    {
        $this->dossier = $dossier;

        return $this;
    }

    public function getDateCreation(): \DateTimeImmutable
    {
        return $this->dateCreation;
    }

    public function getDateActivation(): ?\DateTimeImmutable
    {
        return $this->dateActivation;
    }

    public function activer(): RequeteSignatureRequerant
    {
        $this->dateActivation = new \DateTimeImmutable();

        return $this;
    }

    public function estActive(): bool
    {
        return null !== $this->dateActivation;
    }

    public function estASigner(): bool
    {
        return $this->estActive() && !$this->estSignee() && !$this->estExpiree() && !$this->estAnnulee();
    }

    public function estSignee(): bool
    {
        return null !== $this->dateSignature;
    }

    public function setSignee(): self
    {
        $this->dateSignature = new \DateTimeImmutable();

        return $this;
    }

    public function getDateSignature(): ?\DateTimeImmutable
    {
        return $this->dateSignature;
    }

    public function setDateSignature(?\DateTimeImmutable $dateSignature): RequeteSignatureRequerant
    {
        $this->dateSignature = $dateSignature;

        return $this;
    }

    public function getDateAnnulation(): ?\DateTimeImmutable
    {
        return $this->dateAnnulation;
    }

    public function annuler(): RequeteSignatureRequerant
    {
        $this->dateAnnulation = new \DateTimeImmutable();

        return $this;
    }

    public function estAnnulee(): bool
    {
        return null !== $this->dateAnnulation;
    }

    /**
     * Une requête fermée (expirée ou annulée) ne peut plus être signée : elle peut être remplacée par une nouvelle.
     */
    public function estFermee(): bool
    {
        return $this->estExpiree() || $this->estAnnulee();
    }

    public function getDateExpiration(): ?\DateTimeImmutable
    {
        return $this->dateExpiration;
    }

    public function setDateExpiration(?\DateTimeImmutable $dateExpiration): RequeteSignatureRequerant
    {
        $this->dateExpiration = $dateExpiration;

        return $this;
    }

    public function estExpiree(): bool
    {
        return null !== $this->dateExpiration && $this->dateExpiration <= new \DateTimeImmutable();
    }

    public function getUrlSignatureExterne(): ?string
    {
        return $this->urlSignatureExterne;
    }

    public function setUrlSignatureExterne(?string $urlSignatureExterne): RequeteSignatureRequerant
    {
        $this->urlSignatureExterne = $urlSignatureExterne;

        return $this;
    }

    public function getReferenceBancaire(): ?ReferenceBancaire
    {
        return $this->referenceBancaire;
    }

    public function setReferenceBancaire(?ReferenceBancaire $referenceBancaire): RequeteSignatureRequerant
    {
        $this->referenceBancaire = $referenceBancaire;

        return $this;
    }
}
