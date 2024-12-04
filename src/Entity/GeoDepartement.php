<?php

namespace App\Entity;

use App\Repository\AgentRepository;
use App\Repository\GeoDepartementRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Table(name: 'geo_departements')]
#[ORM\Entity(repositoryClass: GeoDepartementRepository::class)]
class GeoDepartement
{
    #[ORM\Id]
    #[ORM\Column(length: 3)]
    private string $code;

    #[ORM\Column(length: 32)]
    private string $nom;

    #[ORM\ManyToOne(targetEntity: GeoRegion::class, cascade: ['persist', 'remove'], inversedBy: 'departements')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE', referencedColumnName: 'code', name: 'region_code')]
    protected GeoRegion $region;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    protected bool $estDeploye = false;

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
        return $this->estDeploye;
    }

    public function getLibelle(): string
    {
        return sprintf("%s - (%s)", $this->nom, str_pad($this->code,2, '0',STR_PAD_LEFT));
    }

    public function __toString(): string
    {
        return $this->nom;
    }
}
