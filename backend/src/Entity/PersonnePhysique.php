<?php

namespace MonIndemnisationJustice\Entity;

use ApiPlatform\Metadata\ApiProperty;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use MonIndemnisationJustice\Repository\PersonnePhysiqueRepository;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

#[ORM\Table(name: 'personnes_physiques')]
#[ORM\Entity(repositoryClass: PersonnePhysiqueRepository::class)]
#[ORM\HasLifecycleCallbacks]
class PersonnePhysique
{
    #[Groups(['dossier:lecture', 'dossier:patch'])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Personne::class, inversedBy: 'personnePhysique', cascade: ['persist', 'remove'])]
    protected Personne $personne;

    #[Groups(['dossier:lecture', 'dossier:patch'])]
    #[ORM\Column(length: 13, nullable: true)]
    private ?string $numeroSecuriteSociale = null;

    #[Groups(['dossier:lecture', 'dossier:patch'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prenom2 = null;

    #[Groups(['dossier:lecture', 'dossier:patch'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prenom3 = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    protected ?Adresse $adresse;

    #[Groups(['dossier:lecture', 'dossier:patch'])]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateNaissance = null;

    #[Groups(['dossier:lecture', 'dossier:patch'])]
    // #[ApiProperty(readableLink: false, writableLink: false, genId: true)]
    #[SerializedName('communeNaissance')]
    #[ORM\ManyToOne(targetEntity: GeoCodePostal::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'code_postal_naissance_id', referencedColumnName: 'id')]
    public ?GeoCodePostal $codePostalNaissance = null;

    #[Groups(['dossier:lecture', 'dossier:patch'])]
    #[ApiProperty(readableLink: false, writableLink: false, genId: true)]
    #[ORM\ManyToOne(targetEntity: GeoPays::class)]
    #[ORM\JoinColumn(name: 'pays_naissance', referencedColumnName: 'code')]
    protected ?GeoPays $paysNaissance = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    protected ?string $villeNaissance = null;

    #[ORM\OneToMany(targetEntity: Dossier::class, mappedBy: 'requerantPersonnePhysique')]
    /** @var Collection<Dossier> */
    protected Collection $dossiers;

    public function __toString()
    {
        return $this->personne->__toString();
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

    public function getNumeroSecuriteSociale(): ?string
    {
        return $this->numeroSecuriteSociale;
    }

    public function recalculerNumeroSecuriteSociale(): void
    {
        if (null !== $this->personne->getCivilite() && null !== $this->dateNaissance && (
                null !== $this->codePostalNaissance || (null !== $this->paysNaissance && !$this->paysNaissance->estFrance())
            )
        ) {
            $this->dateNaissance->format('m');
            $this->numeroSecuriteSociale = sprintf(
                '%d%s%s%s',
                $this->personne->getCivilite()->getCode(),
                $this->dateNaissance->format('y'),
                $this->dateNaissance->format('m'),
                $this->codePostalNaissance ? $this->codePostalNaissance->getCommune()->getCode() : $this->paysNaissance->getCodeInsee()
            );
        }
    }

    public function getPersonne(): Personne
    {
        return $this->personne;
    }

    public function setPersonne(Personne $personne): PersonnePhysique
    {
        $this->personne = $personne;

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
        return implode(', ', array_filter([$this->personne->getPrenom(), $this->prenom2, $this->prenom3], fn($prenom) => !empty($prenom)));
    }

    public function getAdresse(): ?Adresse
    {
        return $this->adresse;
    }

    public function setAdresse(?Adresse $adresse): PersonnePhysique
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getCodePostalNaissance(): ?GeoCodePostal
    {
        return $this->codePostalNaissance;
    }

    public function setCodePostalNaissance(?GeoCodePostal $codePostalNaissance): PersonnePhysique
    {
        $this->codePostalNaissance = $codePostalNaissance;
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

    public function getVilleNaissance(): ?string
    {
        return $this->villeNaissance;
    }

    public function setVilleNaissance(?string $villeNaissance): PersonnePhysique
    {
        $this->villeNaissance = $villeNaissance;

        return $this;
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

    /**
     * Retourne le nom prénom de la personne, éventuellement précédé par la civilité de la personne si $civilite et le
     * nom de famille en lettres capitales si $capitale.
     */
    public function getNomCourant(bool $civilite = false, bool $capital = false): string
    {
        return $this->personne->getNomCourant($civilite, $capital);
    }

    public function getNomComplet(): string
    {
        return $this->personne->getNomComplet();
    }
}
