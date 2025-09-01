<?php

namespace MonIndemnisationJustice\Entity;

use Doctrine\ORM\Mapping as ORM;
use MonIndemnisationJustice\Repository\GeoDepartementRepository;

#[ORM\Table(name: 'geo_departements')]
#[ORM\Entity(repositoryClass: GeoDepartementRepository::class)]
#[ORM\HasLifecycleCallbacks]
class GeoDepartement extends GeoDataEntity
{
    #[ORM\Id]
    #[ORM\Column(length: 3)]
    protected string $code;

    #[ORM\Column(length: 255)]
    protected string $nom;

    #[ORM\ManyToOne(targetEntity: GeoRegion::class, cascade: ['persist', 'remove'], inversedBy: 'departements')]
    #[ORM\JoinColumn(onDelete: 'CASCADE', referencedColumnName: 'code', name: 'region_code')]
    protected ?GeoRegion $region = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    protected bool $estDeploye = false;

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): GeoDepartement
    {
        $this->code = $code;

        return $this;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): GeoDepartement
    {
        $this->nom = $nom;

        return $this;
    }

    public function getRegion(): GeoRegion
    {
        return $this->region;
    }

    public function setRegion(?GeoRegion $region): GeoDepartement
    {
        $this->region = $region;

        return $this;
    }

    public function estDeploye(): bool
    {
        return $this->estDeploye;
    }

    public function setDeploye(bool $estDeploye): GeoDepartement
    {
        $this->estDeploye = $estDeploye;

        return $this;
    }

    public function getLibelle(): string
    {
        return sprintf('%s - %s', str_pad($this->code, 2, '0', STR_PAD_LEFT), $this->nom);
    }

    public function __toString(): string
    {
        return $this->nom;
    }
}
