<?php

namespace MonIndemnisationJustice\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'geo_regions')]
#[ORM\Entity]
class GeoRegion
{
    #[ORM\Id]
    #[ORM\Column(length: 2)]
    protected string $code;

    #[ORM\Column(length: 32)]
    protected string $nom;

    /**
     * @var Collection<GeoDepartement> $departements
     */
    #[ORM\OneToMany(targetEntity: GeoDepartement::class, mappedBy: 'region', cascade: ['remove'])]
    protected Collection $departements;

    public function getCode(): string
    {
        return $this->code;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function estDeploye(): bool
    {
        return $this->departements->forAll(fn (GeoDepartement $departement) => $departement->estDeploye());
    }
}
