<?php

namespace MonIndemnisationJustice\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'dossier_courriers')]
class CourrierDossier
{
    #[Groups('agent:detail')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['dossier:lecture'])]
    private ?string $filename = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    protected \DateTimeInterface $dateCreation;

    #[ORM\ManyToOne(targetEntity: BrisPorte::class, inversedBy: 'historiqueCourriers', cascade: ['detach'])]
    #[ORM\JoinColumn(nullable: false, name: 'dossier_id', referencedColumnName: 'id')]
    protected ?BrisPorte $dossier;

    #[ORM\ManyToOne(targetEntity: Agent::class, cascade: ['detach'])]
    #[ORM\JoinColumn(name: 'agent_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?Agent $agent;

    #[ORM\ManyToOne(targetEntity: Requerant::class, cascade: ['detach'])]
    #[ORM\JoinColumn(name: 'requerant_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?Requerant $requerant;

    public function getId(): ?int
    {
        return $this->id;
    }

    #[Groups('agent:detail')]
    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(?string $filename): CourrierDossier
    {
        $this->filename = $filename;

        return $this;
    }

    public function getDateCreation(): \DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): CourrierDossier
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getDossier(): ?BrisPorte
    {
        return $this->dossier;
    }

    public function setDossier(?BrisPorte $dossier): CourrierDossier
    {
        $this->dossier = $dossier;

        return $this;
    }

    public function getAgent(): ?Agent
    {
        return $this->agent;
    }

    public function setAgent(?Agent $agent): CourrierDossier
    {
        $this->agent = $agent;

        return $this;
    }

    public function getRequerant(): ?Requerant
    {
        return $this->requerant;
    }

    public function setRequerant(?Requerant $requerant): CourrierDossier
    {
        $this->requerant = $requerant;

        return $this;
    }

    public function getFileHash(): string
    {
        return md5($this->filename);
    }
}
