<?php

namespace MonIndemnisationJustice\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'brouillons_declaration_fdo_bris_porte')]
#[ORM\HasLifecycleCallbacks]
class BrouillonDeclarationFDOBrisPorte
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[Groups(['agent:detail'])]
    protected ?Uuid $id = null;

    /**
     * @var Agent l'agent des FDO déclarant
     */
    #[ORM\ManyToOne(targetEntity: Agent::class, cascade: [])]
    #[ORM\JoinColumn(name: 'agent_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[Groups(['agent:detail'])]
    protected Agent $agent;

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

    #[ORM\PostPersist]
    public function postPersist(PostPersistEventArgs $args): void
    {
        $this->ajouterDonnees([
            'id' => $this->id,
        ]);
        $args->getObjectManager()->persist($this);
        $args->getObjectManager()->flush();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function setId(?Uuid $id): BrouillonDeclarationFDOBrisPorte
    {
        $this->id = $id;

        return $this;
    }

    public function getAgent(): Agent
    {
        return $this->agent;
    }

    public function setAgent(Agent $agent): BrouillonDeclarationFDOBrisPorte
    {
        $this->agent = $agent;

        return $this;
    }

    public function getDateCreation(): \DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): BrouillonDeclarationFDOBrisPorte
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getDonnees(): array
    {
        return $this->donnees;
    }

    public function ajouterDonnees(array $donnees): BrouillonDeclarationFDOBrisPorte
    {
        $this->donnees = array_replace_recursive(
            $this->donnees ?? [],
            $donnees
        );

        return $this;
    }

    public function setDonnees(array $donnees): BrouillonDeclarationFDOBrisPorte
    {
        $this->donnees = $donnees;

        return $this;
    }
}
