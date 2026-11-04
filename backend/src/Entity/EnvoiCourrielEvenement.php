<?php

namespace MonIndemnisationJustice\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * Décrit l'envoi d'un courriel.
 */
#[ORM\Table(name: 'courriels_evenements')]
#[ORM\Entity]
class EnvoiCourrielEvenement
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    protected Uuid $id;

    #[ORM\ManyToOne(targetEntity: EnvoiCourriel::class, inversedBy: 'evenements')]
    #[ORM\JoinColumn(name: 'envoi_courriel_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected EnvoiCourriel $courriel;

    #[ORM\Column(type: 'string', nullable: false, enumType: EnvoiCourrielEvenementType::class)]
    protected EnvoiCourrielEvenementType $type;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    protected \DateTimeImmutable $date;

    #[ORM\Column(type: 'json', nullable: true)]
    protected ?array $contexte = null;

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getCourriel(): EnvoiCourriel
    {
        return $this->courriel;
    }

    public function setCourriel(EnvoiCourriel $courriel): EnvoiCourrielEvenement
    {
        $this->courriel = $courriel;

        return $this;
    }

    public function getType(): EnvoiCourrielEvenementType
    {
        return $this->type;
    }

    public function setType(EnvoiCourrielEvenementType $type): EnvoiCourrielEvenement
    {
        $this->type = $type;

        return $this;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): EnvoiCourrielEvenement
    {
        $this->date = $date;

        return $this;
    }

    public function getContexte(): ?array
    {
        return $this->contexte;
    }

    public function setContexte(?array $contexte): EnvoiCourrielEvenement
    {
        $this->contexte = $contexte;

        return $this;
    }

    public function equivautA(EnvoiCourrielEvenement $evenement): bool
    {
        return $this->courriel === $evenement->getCourriel()
            && $this->type === $evenement->getType()
            && $this->getDate()->getTimestamp() === $evenement->getDate()->getTimestamp();
    }
}
