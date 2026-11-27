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

    /**
     * Relevé d'identité bancaire (PDF) généré à partir de ces coordonnées, transmis au bureau du budget.
     */
    #[ORM\OneToOne(targetEntity: Document::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'document_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    protected ?Document $document = null;

    public function __construct(TypeReferenceBancaire $type, string $titulaireDuCompte)
    {
        $this->type = $type;
        $this->titulaireDuCompte = $titulaireDuCompte;
        $this->dateCreation = new \DateTimeImmutable();
    }

    public static function depuisIban(string $iban, string $titulaireDuCompte): self
    {
        return new self(TypeReferenceBancaire::IBAN, $titulaireDuCompte)->definirIban($iban);
    }

    public static function depuisRib(string $codeBanque, string $codeGuichet, string $numeroCompte, string $cleRib, string $titulaireDuCompte): self
    {
        return new self(TypeReferenceBancaire::RIB, $titulaireDuCompte)->definirRib($codeBanque, $codeGuichet, $numeroCompte, $cleRib);
    }

    /**
     * Passe la référence en IBAN : les champs du RIB sont effacés.
     */
    public function definirIban(string $iban): self
    {
        $this->type = TypeReferenceBancaire::IBAN;
        $this->iban = self::normaliser($iban);
        $this->codeBanque = $this->codeGuichet = $this->numeroCompte = $this->cleRib = null;

        return $this;
    }

    /**
     * Passe la référence en RIB : l'IBAN est effacé.
     */
    public function definirRib(string $codeBanque, string $codeGuichet, string $numeroCompte, string $cleRib): self
    {
        $this->type = TypeReferenceBancaire::RIB;
        $this->codeBanque = self::normaliser($codeBanque);
        $this->codeGuichet = self::normaliser($codeGuichet);
        $this->numeroCompte = self::normaliser($numeroCompte);
        $this->cleRib = self::normaliser($cleRib);
        $this->iban = null;

        return $this;
    }

    /**
     * Les 4 champs du RIB : saisis, ou extraits d'un IBAN français complet (`FRkk BBBBB GGGGG CCCCCCCCCCC KK`).
     *
     * @return array{codeBanque: string, codeGuichet: string, numeroCompte: string, cleRib: string}|null
     */
    public function getChampsRib(): ?array
    {
        if (TypeReferenceBancaire::RIB === $this->type) {
            return [
                'codeBanque' => $this->codeBanque ?? '',
                'codeGuichet' => $this->codeGuichet ?? '',
                'numeroCompte' => $this->numeroCompte ?? '',
                'cleRib' => $this->cleRib ?? '',
            ];
        }

        if ($this->estIbanFrancais() && 27 === strlen($this->iban)) {
            return [
                'codeBanque' => substr($this->iban, 4, 5),
                'codeGuichet' => substr($this->iban, 9, 5),
                'numeroCompte' => substr($this->iban, 14, 11),
                'cleRib' => substr($this->iban, 25, 2),
            ];
        }

        return null;
    }

    /**
     * L'IBAN : saisi, ou calculé à partir d'un RIB complet (ISO 13616 : `FR` + clé de contrôle + RIB).
     */
    public function getIbanComplet(): ?string
    {
        if (TypeReferenceBancaire::IBAN === $this->type) {
            return $this->iban;
        }

        $bban = sprintf('%s%s%s%s', $this->codeBanque, $this->codeGuichet, $this->numeroCompte, $this->cleRib);

        if (!preg_match('/^[0-9A-Z]{23}$/', $bban)) {
            return null;
        }

        // Lettres converties en nombres (A = 10, …, Z = 35), suivies de `FR00` converti (F = 15, R = 27)
        $chiffres = preg_replace_callback('/[A-Z]/', fn (array $lettre) => (string) (ord($lettre[0]) - ord('A') + 10), $bban).'152700';
        $reste = array_reduce(str_split($chiffres, 7), fn (int $reste, string $bloc) => (int) ($reste.$bloc) % 97, 0);

        return sprintf('FR%02d%s', 98 - $reste, $bban);
    }

    /**
     * Coordonnées lisibles, telles qu'imprimées dans les documents : IBAN groupé par 4, ou les 4 champs du RIB.
     */
    public function getLibelle(): string
    {
        return TypeReferenceBancaire::IBAN === $this->type
            ? 'IBAN '.trim(chunk_split($this->iban ?? '', 4, ' '))
            : sprintf('RIB %s %s %s %s', $this->codeBanque, $this->codeGuichet, $this->numeroCompte, $this->cleRib);
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

    public function getDocument(): ?Document
    {
        return $this->document;
    }

    public function setDocument(?Document $document): ReferenceBancaire
    {
        $this->document = $document;

        return $this;
    }
}
