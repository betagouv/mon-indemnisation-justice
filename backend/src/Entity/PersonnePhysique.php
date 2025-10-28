<?php

namespace MonIndemnisationJustice\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use MonIndemnisationJustice\Repository\PersonnePhysiqueRepository;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

#[ORM\Entity(repositoryClass: PersonnePhysiqueRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource]
class PersonnePhysique
{
    #[Groups(['dossier:lecture', 'dossier:patch'])]
    // #[ApiProperty(readableLink: false, writableLink: false, genId: true)]
    #[SerializedName('communeNaissance')]
    #[ORM\ManyToOne(targetEntity: GeoCodePostal::class)]
    #[ORM\JoinColumn(name: 'code_postal_naissance_id', referencedColumnName: 'id')]
    public ?GeoCodePostal $codePostalNaissance = null;

    #[ORM\OneToOne(mappedBy: 'personnePhysique', cascade: ['persist', 'remove'])]
    protected ?Requerant $compte = null;

    #[Groups(['dossier:lecture', 'dossier:patch'])]
    #[ORM\Column(type: 'string', length: 3, nullable: true, enumType: Civilite::class)]
    protected ?Civilite $civilite = null;

    #[Groups(['dossier:lecture', 'dossier:patch'])]
    #[ApiProperty(readableLink: false, writableLink: false, genId: true)]
    #[ORM\ManyToOne(targetEntity: GeoPays::class)]
    #[ORM\JoinColumn(name: 'pays_naissance', referencedColumnName: 'code')]
    protected ?GeoPays $paysNaissance = null;
    #[ApiProperty(identifier: true)]
    #[Groups(['dossier:lecture', 'dossier:patch'])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['dossier:lecture', 'dossier:patch'])]
    #[ORM\Column(length: 13, nullable: true)]
    private ?string $numeroSecuriteSociale = null;

    #[Groups(['dossier:lecture', 'dossier:patch'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[Groups(['dossier:lecture', 'dossier:patch'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prenom1 = null;

    #[Groups(['dossier:lecture', 'dossier:patch'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prenom2 = null;

    #[Groups(['dossier:lecture', 'dossier:patch'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prenom3 = null;

    #[Groups(['dossier:lecture', 'dossier:patch'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telephone = null;

    #[Groups(['dossier:lecture', 'dossier:patch'])]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateNaissance = null;

    #[Groups(['dossier:lecture', 'dossier:patch'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomNaissance = null;

    #[Groups(['dossier:lecture', 'dossier:patch'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    public function __toString()
    {
        return implode(' ', [
            $this->getCivilite()?->value,
            ucfirst(strtolower($this->getPrenom1())),
            strtoupper($this->getNomNaissance()),
        ]);
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->recalculerNumeroSecuriteSociale();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->recalculerNumeroSecuriteSociale();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCivilite(): ?Civilite
    {
        return $this->civilite;
    }

    public function setCivilite(?Civilite $civilite): self
    {
        if (null !== $civilite) {
            $this->civilite = $civilite;
        }

        return $this;
    }

    public function getNumeroSecuriteSociale(): ?string
    {
        return $this->numeroSecuriteSociale;
    }

    public function recalculerNumeroSecuriteSociale(): void
    {
        if (null !== $this->civilite && null !== $this->dateNaissance && (
            null !== $this->codePostalNaissance || (null !== $this->paysNaissance && !$this->paysNaissance->estFrance())
        )
        ) {
            $this->dateNaissance->format('m');
            $this->numeroSecuriteSociale = sprintf(
                '%d%s%s%s',
                $this->civilite->getCode(),
                $this->dateNaissance->format('y'),
                $this->dateNaissance->format('m'),
                $this->codePostalNaissance ? $this->codePostalNaissance->getCommune()->getCode() : $this->paysNaissance->getCodeInsee()
            );
        }
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom1(): ?string
    {
        return $this->prenom1;
    }

    public function setPrenom1(?string $prenom1): static
    {
        $this->prenom1 = $prenom1;

        return $this;
    }

    public function getPrenom2(): ?string
    {
        return $this->prenom2;
    }

    public function setPrenom2(?string $prenom2): static
    {
        $this->prenom2 = $prenom2;

        return $this;
    }

    public function getPrenom3(): ?string
    {
        return $this->prenom3;
    }

    public function setPrenom3(?string $prenom3): static
    {
        $this->prenom3 = $prenom3;

        return $this;
    }

    public function getPrenoms(): ?string
    {
        return implode(', ', array_filter([$this->prenom1, $this->prenom2, $this->prenom3], fn ($prenom) => !empty($prenom)));
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getCommuneNaissance(): ?string
    {
        return $this->codePostalNaissance?->getCommune()->getNom();
    }

    public function setCommuneNaissance(?GeoCodePostal $codePostal = null): static
    {
        $this->codePostalNaissance = $codePostal;

        return $this;
    }

    #[SerializedName('codePostalNaissance')]
    #[Groups(['dossier:lecture'])]
    public function getCodePostalNaissanceCode(): ?string
    {
        return $this->codePostalNaissance?->getCodePostal();
    }

    public function getPaysNaissance(): ?GeoPays
    {
        return $this->paysNaissance;
    }

    public function setPaysNaissance(?GeoPays $paysNaissance = null): static
    {
        $this->paysNaissance = $paysNaissance;

        return $this;
    }

    public function getDateNaissance(): ?\DateTimeInterface
    {
        return $this->dateNaissance;
    }

    public function setDateNaissance(?\DateTimeInterface $dateNaissance): static
    {
        $this->dateNaissance = $dateNaissance;

        return $this;
    }

    public function getNomNaissance(): ?string
    {
        return $this->nomNaissance;
    }

    public function setNomNaissance(?string $nomNaissance): static
    {
        $this->nomNaissance = $nomNaissance;

        return $this;
    }

    public function getNomCourant(bool $civilite = false, bool $capital = false): string
    {
        return sprintf(
            '%s%s %s',
            $civilite ? ucfirst(strtolower($this->civilite->value)).'. ' : '',
            $this->prenom1,
            $capital ? strtoupper($this->nom) : ucfirst($this->nom)
        );
    }

    public function getNomComplet(): string
    {
        $similarite = 0;
        if (null !== $this->nom && null !== $this->prenom1) {
            similar_text($this->nom, $this->nomNaissance, $similarite);
        }

        return sprintf(
            '%s %s %s %s',
            $this->civilite->getLibelle(),
            strtoupper($this->nom),
            empty($this->nomNaissance) || $similarite > 80 ? '' : $this->civilite->libelleNaissance($this->nomNaissance),
            $this->prenom1
        );
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }
}
