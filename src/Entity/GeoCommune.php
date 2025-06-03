<?php

namespace MonIndemnisationJustice\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'geo_communes')]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class GeoCommune extends GeoDataEntity
{
    #[ORM\Id]
    #[ORM\Column(length: 5)]
    /** Le code INSEE */
    protected string $code;

    #[ORM\Column]
    protected string $nom;

    #[ORM\Column(type: 'simple_array')]
    protected array $codePostaux;

    #[ORM\ManyToOne(targetEntity: GeoDepartement::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'departement_code', referencedColumnName: 'code', nullable: false, onDelete: 'SET NULL')]
    protected GeoDepartement $departement;

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): GeoCommune
    {
        $this->code = $code;

        return $this;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): GeoCommune
    {
        $this->nom = $nom;

        return $this;
    }

    public function getCodePostaux(): array
    {
        return $this->codePostaux;
    }

    public function setCodePostaux(array $codePostaux): GeoCommune
    {
        $this->codePostaux = $codePostaux;

        return $this;
    }

    public function getDepartement(): GeoDepartement
    {
        return $this->departement;
    }

    public function setDepartement(GeoDepartement $departement): GeoCommune
    {
        $this->departement = $departement;

        return $this;
    }
}
