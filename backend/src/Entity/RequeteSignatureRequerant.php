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

    #[ORM\OneToOne(targetEntity: Dossier::class, inversedBy: 'requeteSignatureRequerant', cascade: ['persist', 'remove'])]
    protected Dossier $dossier;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    protected \DateTimeImmutable $dateCreation;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    protected ?\DateTimeImmutable $dateSignature = null;

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

    public function setDateCreation(\DateTimeImmutable $dateCreation): RequeteSignatureRequerant
    {
        $this->dateCreation = $dateCreation;

        return $this;
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
}
