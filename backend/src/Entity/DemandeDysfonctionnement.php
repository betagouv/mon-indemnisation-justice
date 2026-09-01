<?php

namespace MonIndemnisationJustice\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'demandes_dysfonctionnement')]
class DemandeDysfonctionnement
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    protected ?Uuid $id = null;

    #[ORM\OneToOne(targetEntity: Dossier::class, mappedBy: 'demandeDysfonctionnement')]
    protected Dossier $dossier;

    #[ORM\OneToOne(targetEntity: TestEligibiliteDysfonctionnement::class, inversedBy: 'dossier')]
    #[ORM\JoinColumn(name: 'test_eligibilite_id', nullable: true, onDelete: 'SET NULL')]
    protected ?TestEligibiliteDysfonctionnement $testEligibilite = null;

    #[ORM\Column(length: 16, enumType: ProfilDeposant::class, nullable: true)]
    protected ?ProfilDeposant $profil = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $identiteNom = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $identitePrenom = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $identiteCourriel = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $identiteCabinet = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $identiteBarreau = null;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getDossier(): Dossier
    {
        return $this->dossier;
    }

    public function getTestEligibilite(): ?TestEligibiliteDysfonctionnement
    {
        return $this->testEligibilite;
    }

    public function setTestEligibilite(?TestEligibiliteDysfonctionnement $testEligibilite): self
    {
        $this->testEligibilite = $testEligibilite;

        return $this;
    }

    public function getProfil(): ?ProfilDeposant
    {
        return $this->profil;
    }

    public function setProfil(?ProfilDeposant $profil): self
    {
        $this->profil = $profil;

        return $this;
    }

    public function getIdentiteNom(): ?string
    {
        return $this->identiteNom;
    }

    public function setIdentiteNom(?string $identiteNom): self
    {
        $this->identiteNom = $identiteNom;

        return $this;
    }

    public function getIdentitePrenom(): ?string
    {
        return $this->identitePrenom;
    }

    public function setIdentitePrenom(?string $identitePrenom): self
    {
        $this->identitePrenom = $identitePrenom;

        return $this;
    }

    public function getIdentiteCourriel(): ?string
    {
        return $this->identiteCourriel;
    }

    public function setIdentiteCourriel(?string $identiteCourriel): self
    {
        $this->identiteCourriel = $identiteCourriel;

        return $this;
    }

    public function getIdentiteCabinet(): ?string
    {
        return $this->identiteCabinet;
    }

    public function setIdentiteCabinet(?string $identiteCabinet): self
    {
        $this->identiteCabinet = $identiteCabinet;

        return $this;
    }

    public function getIdentiteBarreau(): ?string
    {
        return $this->identiteBarreau;
    }

    public function setIdentiteBarreau(?string $identiteBarreau): self
    {
        $this->identiteBarreau = $identiteBarreau;

        return $this;
    }
}
