<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait ReferentielTrait
{
    #[ORM\Column(length: 50, unique: true)]
    #[Groups(["read"])]
    private ?string $code = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(["read"])]
    private ?string $mnemo = null;

    #[ORM\Column(length: 255)]
    #[Groups(["read"])]
    private ?string $libelle = null;

    public string $libelleLong;
    public string $libelleCourt;
    public string $libelleAvecMnemo;

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getLibelleAvecMnemo(): string
    {
        if($this->getLibelle())
          return ucfirst($this->getLibelle()).' ('.$this->getMnemo().')';
        return "";
    }
    public function getLibelleLong(): string
    {
        return ucfirst($this->getLibelle()).' ('.$this->getCode().')';
    }

    public function getLibelleCourt(): string
    {
        return ucfirst($this->getLibelle());
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getMnemo(): ?string
    {
        return $this->mnemo;
    }

    public function setMnemo(?string $mnemo): self
    {
        $this->mnemo = $mnemo;

        return $this;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }
}
