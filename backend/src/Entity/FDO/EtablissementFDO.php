<?php

namespace MonIndemnisationJustice\Entity\FDO;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use MonIndemnisationJustice\Entity\Administration;
use MonIndemnisationJustice\Entity\Adresse;
use MonIndemnisationJustice\Entity\GeoCodePostal;
use MonIndemnisationJustice\Repository\EtablissementFDORepository;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: EtablissementFDORepository::class)]
#[ORM\Table(name: 'fdo_etablissements')]
#[ORM\Index(name: 'unique_nom_etablissement', columns: ['nom'])]
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
    protected string $nom;

    #[ORM\Column(type: 'string', length: 16)]
    protected ?string $identifiant = null;

    #[ORM\OneToOne(Adresse::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'adresse_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected ?Adresse $adresse = null;

    #[ORM\ManyToOne(GeoCodePostal::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'code_postal_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected GeoCodePostal $codePostal;
    /**
     * @var Collection<GeoCodePostal>
     */
    #[ORM\ManyToMany(targetEntity: GeoCodePostal::class, cascade: [], orphanRemoval: true)]
    #[ORM\JoinTable(name: 'fdo_etablissements_code_postaux')]
    #[ORM\JoinColumn(name: 'etablissement_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'code_postal_id', referencedColumnName: 'id')]
    protected Collection $competences;

    #[ORM\Column(type: 'string', length: 16, nullable: true)]
    protected ?string $telephone = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    protected ?string $courriel = null;

    public function __construct()
    {
        $this->competences = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): EtablissementFDO
    {
        $this->nom = $nom;

        return $this;
    }

    public function getAdministration(): ?Administration
    {
        return $this->administration;
    }

    public function setAdministration(?Administration $administration): EtablissementFDO
    {
        $this->administration = $administration;

        return $this;
    }

    public function getAdresse(): ?Adresse
    {
        return $this->adresse;
    }

    public function setAdresse(?Adresse $adresse): EtablissementFDO
    {
        // Si une adresse est donnée alors que l'adresse est déjà définie, on fusionne
        if (null !== $this->adresse && null !== $adresse) {
            $this->adresse->fusionner($adresse);
        } else {
            $this->adresse = $adresse;
        }

        return $this;
    }

    public function getCodePostal(): GeoCodePostal
    {
        return $this->codePostal;
    }

    public function setCodePostal(GeoCodePostal $codePostal): EtablissementFDO
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    public function getCompetences(): Collection
    {
        return $this->competences;
    }

    public function ajouterCompetence(GeoCodePostal $codePostal): EtablissementFDO
    {
        $this->competences->add($codePostal);

        return $this;
    }

    public function getCourriel(): ?string
    {
        return $this->courriel;
    }

    public function setCourriel(?string $courriel): EtablissementFDO
    {
        $this->courriel = $courriel;

        return $this;
    }

    public function getIdentifiant(): ?string
    {
        return $this->identifiant;
    }

    public function setIdentifiant(?string $identifiant): EtablissementFDO
    {
        $this->identifiant = $identifiant;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): EtablissementFDO
    {
        $this->telephone = $telephone;

        return $this;
    }
}
