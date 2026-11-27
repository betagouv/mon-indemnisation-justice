<?php

namespace MonIndemnisationJustice\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Établissement bancaire français, identifié par son code banque (code interbancaire attribué par l'ACPR), tel qu'il
 * figure sur un RIB ou dans un IBAN français. Sert à suggérer le nom de la banque à partir des coordonnées saisies.
 *
 * Provisionné via la commande `mij:importer:etablissements-bancaires`.
 */
#[ORM\Entity]
#[ORM\Table(name: 'etablissements_bancaires')]
#[ORM\HasLifecycleCallbacks]
class EtablissementBancaire
{
    #[ORM\Id]
    #[ORM\Column(length: 5)]
    protected string $codeBanque;

    #[ORM\Column]
    protected string $nom;

    /**
     * Nom du fichier du logo, servi depuis `public/images/logos/etablissements_bancaires/`.
     */
    #[ORM\Column(length: 100, nullable: true)]
    protected ?string $logo = null;

    #[ORM\Column(nullable: false, options: ['default' => true])]
    protected bool $estActif = true;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    protected \DateTimeImmutable $dateDerniereMaj;

    public function __construct()
    {
        $this->dateDerniereMaj = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onUpdate(): void
    {
        $this->dateDerniereMaj = new \DateTimeImmutable();
    }

    public function getCodeBanque(): string
    {
        return $this->codeBanque;
    }

    public function setCodeBanque(string $codeBanque): EtablissementBancaire
    {
        $this->codeBanque = $codeBanque;

        return $this;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): EtablissementBancaire
    {
        $this->nom = $nom;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): EtablissementBancaire
    {
        $this->logo = $logo;

        return $this;
    }

    public function estActif(): bool
    {
        return $this->estActif;
    }

    public function setEstActif(bool $estActif): EtablissementBancaire
    {
        $this->estActif = $estActif;

        return $this;
    }

    public function getDateDerniereMaj(): \DateTimeImmutable
    {
        return $this->dateDerniereMaj;
    }
}
