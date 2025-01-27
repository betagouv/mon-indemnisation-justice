<?php

namespace MonIndemnisationJustice\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'agent_fournisseurs_identites')]
class FournisseurIdentiteAgent
{
    public const string RESEAU_INTERNE = 'RIE';

    #[ORM\Id]
    #[ORM\Column]
    protected string $uid;

    #[ORM\Column]
    protected string $nom;

    #[ORM\Column(name: 'est_reseau_interne')]
    protected bool $estReseauInterne;

    #[ORM\Column]
    protected bool $estActif;

    #[ORM\Column]
    protected string $urlDecouverte;

    #[ORM\Column]
    protected array $domaines;

    #[ORM\Column(type: 'string', nullable: true, enumType: Administration::class)]
    protected ?Administration $administration = null;

    public function getUid(): string
    {
        return $this->uid;
    }

    public function setUid(string $uid): FournisseurIdentiteAgent
    {
        $this->uid = $uid;

        return $this;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): FournisseurIdentiteAgent
    {
        $this->nom = $nom;

        return $this;
    }

    public function estReseauInterne(): bool
    {
        return $this->estReseauInterne;
    }

    public function setReseauInterne(bool $estReseauInterne): FournisseurIdentiteAgent
    {
        $this->estReseauInterne = $estReseauInterne;

        return $this;
    }

    public function estActif(): bool
    {
        return $this->estActif;
    }

    public function setActif(bool $estActif): FournisseurIdentiteAgent
    {
        $this->estActif = $estActif;

        return $this;
    }

    public function getUrlDecouverte(): string
    {
        return $this->urlDecouverte;
    }

    public function setUrlDecouverte(string $urlDecouverte): FournisseurIdentiteAgent
    {
        $this->urlDecouverte = $urlDecouverte;

        return $this;
    }

    public function getDomaines(): array
    {
        return $this->domaines;
    }

    public function setDomaines(array $domaines): FournisseurIdentiteAgent
    {
        $this->domaines = $domaines;

        if (null === $this->administration) {
            $this->administration = Administration::fromDomaines($this->domaines);
        }

        return $this;
    }

    public function getAdministration(): ?Administration
    {
        return $this->administration;
    }
}
