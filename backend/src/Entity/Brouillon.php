<?php

namespace MonIndemnisationJustice\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'brouillons')]
#[ORM\HasLifecycleCallbacks]
class Brouillon
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    protected ?Uuid $id = null;

    #[ORM\Column(type: 'string', nullable: false, enumType: BrouillonType::class)]
    protected BrouillonType $type;

    #[ORM\ManyToOne(targetEntity: Usager::class, cascade: [], inversedBy: 'brouillons')]
    #[ORM\JoinColumn(name: 'requerant_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?Usager $usager = null;

    #[ORM\ManyToOne(targetEntity: Agent::class, cascade: [])]
    #[ORM\JoinColumn(name: 'agent_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?Agent $agent = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: false)]
    #[Groups(['agent:detail'])]
    protected \DateTimeInterface $dateCreation;

    #[ORM\Column(type: Types::JSON, nullable: false)]
    protected array $donnees = [];

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        $this->dateCreation = new \DateTimeImmutable();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getType(): BrouillonType
    {
        return $this->type;
    }

    public function setType(BrouillonType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getRequerant(): ?Usager
    {
        return $this->requerant;
    }

    public function setRequerant(?Usager $requerant): self
    {
        $this->requerant = $requerant;

        return $this;
    }

    public function getAgent(): ?Agent
    {
        return $this->agent;
    }

    public function setAgent(?Agent $agent): self
    {
        $this->agent = $agent;

        return $this;
    }

    public function getDateCreation(): \DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function getDonnees(): array
    {
        return $this->donnees;
    }

    public function patchDonnees(array $donnees): self
    {
        return $this->setDonnees(array_merge_recursive($this->donnees ?? [], $donnees));
    }

    public function setDonnees(?array $donnees = []): self
    {
        $this->donnees = $donnees;

        return $this;
    }

    public static function brisDePorte(array $donnees): self
    {
        return new self()
            ->setType(BrouillonType::BROUILLON_DOSSIER_BRIS_PORTE)
            ->setDonnees($donnees);
    }
}
