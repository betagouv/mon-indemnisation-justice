<?php

namespace MonIndemnisationJustice\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * Coordonnées bancaires du compte sur lequel verser l'indemnisation, saisies soit sous forme de RIB, soit d'IBAN
 * (cf. {@see TypeReferenceBancaire}). Le BIC n'est pas demandé : l'IBAN suffit pour un virement SEPA.
 *
 * Aucune validation de format n'est faite ici : le front avertit l'usager de données incohérentes sans le bloquer, et
 * les coordonnées sont ensuite vérifiées par un agent.
 */
#[ORM\Entity]
#[ORM\Table(name: 'references_bancaires')]
class ReferenceBancaire
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    protected ?Uuid $id = null;

    #[ORM\Column(length: 4, enumType: TypeReferenceBancaire::class)]
    protected TypeReferenceBancaire $type;

    // Champs du RIB
    #[ORM\Column(length: 5, nullable: true)]
    protected ?string $codeBanque = null;

    #[ORM\Column(length: 5, nullable: true)]
    protected ?string $codeGuichet = null;

    #[ORM\Column(length: 11, nullable: true)]
    protected ?string $numeroCompte = null;

    #[ORM\Column(length: 2, nullable: true)]
    protected ?string $cleRib = null;

    // Champs de l'IBAN
    #[ORM\Column(length: 34, nullable: true)]
    protected ?string $iban = null;

    #[ORM\Column(nullable: true)]
    protected ?string $nomBanque = null;

    #[ORM\Column]
    protected string $titulaireDuCompte;

    #[ORM\ManyToOne(targetEntity: EtablissementBancaire::class)]
    #[ORM\JoinColumn(name: 'etablissement_code_banque', referencedColumnName: 'code_banque', nullable: true, onDelete: 'SET NULL')]
    protected ?EtablissementBancaire $etablissement = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    protected \DateTimeImmutable $dateCreation;

    public function __construct(TypeReferenceBancaire $type, string $titulaireDuCompte)
    {
        $this->type = $type;
        $this->titulaireDuCompte = $titulaireDuCompte;
        $this->dateCreation = new \DateTimeImmutable();
    }

    public static function depuisIban(string $iban, string $titulaireDuCompte): self
    {
        $reference = new self(TypeReferenceBancaire::IBAN, $titulaireDuCompte);
        $reference->iban = self::normaliser($iban);

        return $reference;
    }

    public static function depuisRib(string $codeBanque, string $codeGuichet, string $numeroCompte, string $cleRib, string $titulaireDuCompte): self
    {
        $reference = new self(TypeReferenceBancaire::RIB, $titulaireDuCompte);
        $reference->codeBanque = self::normaliser($codeBanque);
        $reference->codeGuichet = self::normaliser($codeGuichet);
        $reference->numeroCompte = self::normaliser($numeroCompte);
        $reference->cleRib = self::normaliser($cleRib);

        return $reference;
    }

    /**
     * Retire les espaces et passe en majuscules, sans autre contrôle de format.
     */
    public static function normaliser(string $valeur): string
    {
        return strtoupper(preg_replace('/\s+/', '', $valeur));
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getType(): TypeReferenceBancaire
    {
        return $this->type;
    }

    public function estIbanFrancais(): bool
    {
        return TypeReferenceBancaire::IBAN === $this->type && null !== $this->iban && str_starts_with($this->iban, 'FR');
    }

    /**
     * Le code banque, saisi directement pour un RIB ou extrait d'un IBAN français
     * (`FRkk BBBBB GGGGG CCCCCCCCCCC KK`). Permet le rattachement à un {@see EtablissementBancaire}.
     */
    public function getCodeBanque(): ?string
    {
        if (TypeReferenceBancaire::RIB === $this->type) {
            return $this->codeBanque;
        }

        if ($this->estIbanFrancais() && strlen($this->iban) >= 9) {
            return substr($this->iban, 4, 5);
        }

        return null;
    }

    public function getCodeGuichet(): ?string
    {
        return $this->codeGuichet;
    }

    public function getNumeroCompte(): ?string
    {
        return $this->numeroCompte;
    }

    public function getCleRib(): ?string
    {
        return $this->cleRib;
    }

    public function getIban(): ?string
    {
        return $this->iban;
    }

    public function getNomBanque(): ?string
    {
        return $this->nomBanque;
    }

    public function setNomBanque(?string $nomBanque): ReferenceBancaire
    {
        $this->nomBanque = $nomBanque;

        return $this;
    }

    public function getTitulaireDuCompte(): string
    {
        return $this->titulaireDuCompte;
    }

    public function setTitulaireDuCompte(string $titulaireDuCompte): ReferenceBancaire
    {
        $this->titulaireDuCompte = $titulaireDuCompte;

        return $this;
    }

    public function getEtablissement(): ?EtablissementBancaire
    {
        return $this->etablissement;
    }

    public function setEtablissement(?EtablissementBancaire $etablissement): ReferenceBancaire
    {
        $this->etablissement = $etablissement;

        return $this;
    }

    public function getDateCreation(): \DateTimeImmutable
    {
        return $this->dateCreation;
    }
}
