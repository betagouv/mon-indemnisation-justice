<?php

namespace MonIndemnisationJustice\Entity;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use MonIndemnisationJustice\Repository\GeoCodePostalRepository;

#[ORM\Table(name: 'geo_codes_postaux')]
#[ORM\Entity(repositoryClass: GeoCodePostalRepository::class)]
#[ORM\Index(name: 'idx_code_postal', columns: ['code_postal'])]
#[ORM\UniqueConstraint(name: 'unique_code_insee_postal', fields: ['codePostal', 'commune'])]
#[ORM\HasLifecycleCallbacks]
#[ApiResource]
class GeoCodePostal extends GeoDataEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    protected ?int $id = null;

    #[ORM\Column(name: 'code_postal', length: 5)]
    /** Le code postal */
    protected string $codePostal;

    #[ORM\ManyToOne(targetEntity: GeoCommune::class, cascade: ['persist'], inversedBy: 'codePostaux')]
    #[ORM\JoinColumn(name: 'code_commune', referencedColumnName: 'code', nullable: false)]
    protected GeoCommune $commune;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodePostal(): string
    {
        return $this->codePostal;
    }

    public function setCodePostal(string $codePostal): self
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    public function getCommune(): GeoCommune
    {
        return $this->commune;
    }

    public function setCommune(GeoCommune $commune): GeoCodePostal
    {
        $this->commune = $commune;

        return $this;
    }
}
