<?php

namespace MonIndemnisationJustice\Entity\FDO;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use MonIndemnisationJustice\Entity\Administration;
use MonIndemnisationJustice\Entity\Adresse;
use MonIndemnisationJustice\Entity\GeoCodePostal;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'fdo_etablissements')]
class EtablissementFDO
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    protected ?Uuid $id = null;

    #[ORM\ManyToOne(targetEntity: Administration::class, cascade: [])]
    #[ORM\JoinColumn(name: 'administration_code', referencedColumnName: 'code', nullable: true, onDelete: 'SET NULL')]
    protected ?Administration $administration = null;

    #[ORM\Column(type: 'string')]
    protected nom $nom;
    #[ORM\Column(type: 'string', length: 16)]
    protected ?string $identifiant = null;

    #[ORM\ORM\ManyToOne(Adresse::class)]
    protected ?Adresse $adresse = null;

    #[ORM\ORM\ManyToOne(GeoCodePostal::class)]
    protected GeoCodePostal $codePostal;
    /**
     * @var Collection<GeoCodePostal>
     */
    #[ORM\ManyToMany(targetEntity: GeoCodePostal::class)]
    protected Collection $competences;

    #[ORM\Column(type: 'string', length: 16)]
    protected ?string $telephone = null;

    #[ORM\Column(type: 'string', length: 100)]
    protected ?string $courriel = null;

    public function __construct()
    {
        $this->competences = new ArrayCollection();
    }
}
