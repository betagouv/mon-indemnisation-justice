<?php

namespace MonIndemnisationJustice\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Mapping as ORM;

abstract class GeoDataEntity
{
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false, options: ['default' => 'NOW()'])]
    protected \DateTimeInterface $dateDerniereMaj;

    #[ORM\Column(nullable: false, options: ['default' => true])]
    protected bool $estActif = true;

    #[ORM\PrePersist]
    public function onPrePersist(PrePersistEventArgs $args): void
    {
        $this->dateDerniereMaj = new \DateTime();
    }
}
