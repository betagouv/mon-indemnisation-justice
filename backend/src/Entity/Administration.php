<?php

namespace MonIndemnisationJustice\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use MonIndemnisationJustice\Repository\AdministrationRepository;

#[ORM\Entity(repositoryClass: AdministrationRepository::class)]
#[ORM\Table(name: 'administrations')]
class Administration
{
    #[ORM\Id]
    #[ORM\Column(name: 'code', type: 'string', length: '2', enumType: AdministrationType::class)]
    protected AdministrationType $type;

    #[ORM\Column(name: 'siret', length: 14)]
    protected string $siret;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    protected ?\DateTimeImmutable $dateIntegration = null;

    public function getType(): AdministrationType
    {
        return $this->type;
    }

    public function setType(AdministrationType $type): Administration
    {
        $this->type = $type;

        return $this;
    }

    public function getSiret(): string
    {
        return $this->siret;
    }

    public function setSiret(string $siret): Administration
    {
        $this->siret = $siret;

        return $this;
    }

    public function getDateIntegration(): ?\DateTimeImmutable
    {
        return $this->dateIntegration;
    }

    public function setDateIntegration(\DateTimeImmutable $dateIntegration): Administration
    {
        $this->dateIntegration = $dateIntegration;

        return $this;
    }
}
