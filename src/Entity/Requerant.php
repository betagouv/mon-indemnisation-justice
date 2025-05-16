<?php

namespace MonIndemnisationJustice\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use MonIndemnisationJustice\Repository\RequerantRepository;
use MonIndemnisationJustice\Service\DateConvertisseur;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Entity(repositoryClass: RequerantRepository::class)]
#[ORM\Table(name: 'requerants')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class Requerant implements UserInterface, PasswordAuthenticatedUserInterface
{
    // Le rôle ROLE_REQUERANT est celui donné au porteur d'une requête d'indemnisation
    public const ROLE_REQUERANT = 'ROLE_REQUERANT';

    #[Groups(['user:read', 'dossier:lecture'])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['user:read', 'dossier:lecture'])]
    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column(type: 'simple_array')]
    protected array $roles = [self::ROLE_REQUERANT];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateChangementMDP = null;

    #[ORM\Column(type: 'string', length: 12, nullable: true)]
    protected ?string $jetonVerification;

    #[ORM\Column(type: 'boolean')]
    private $estVerifieCourriel = false;

    #[Groups(['user:read', 'dossier:lecture', 'dossier:patch'])]
    #[ORM\Column(options: ['default' => false])]
    protected bool $isPersonneMorale = false;

    #[Groups(['user:read', 'dossier:lecture', 'dossier:patch'])]
    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Adresse $adresse;

    #[Groups(['user:read', 'dossier:lecture', 'dossier:patch'])]
    #[ORM\OneToOne(inversedBy: 'compte', cascade: ['persist', 'remove'])]
    private ?PersonnePhysique $personnePhysique;

    #[Groups(['dossier:lecture', 'dossier:patch'])]
    #[ORM\OneToOne(inversedBy: 'compte', cascade: ['persist', 'remove'])]
    private ?PersonneMorale $personneMorale;

    #[ORM\OneToMany(targetEntity: BrisPorte::class, mappedBy: 'requerant', cascade: ['remove'])]
    #[ORM\OrderBy(['dateCreation' => 'ASC'])]
    /** @var Collection<BrisPorte> */
    protected Collection $dossiers;

    public function __construct()
    {
        $this->personneMorale = null;
        $this->personnePhysique = new PersonnePhysique();
        $this->adresse = new Adresse();
        $this->dossiers = new ArrayCollection();
    }

    public function getPId(): ?int
    {
        return $this->getId();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    #[Groups('agent:detail')]
    #[SerializedName('courriel')]
    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPlaintextRole(): string
    {
        return '';
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRoles());
    }

    public function addRole(string $role): self
    {
        if (self::ROLE_REQUERANT === $role
            && !in_array($role, $this->roles)
        ) {
            $this->roles[] = $role;
        }

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getUsername(): ?string
    {
        return $this->email;
    }

    public function getDateChangementMDP(): ?\DateTimeInterface
    {
        return $this->dateChangementMDP;
    }

    public function setDateChangementMDP(?\DateTimeInterface $dateChangementMDP): static
    {
        $this->dateChangementMDP = $dateChangementMDP;

        return $this;
    }

    public function getJetonVerification(): ?string
    {
        return $this->jetonVerification;
    }

    public function supprimerJetonVerification(): self
    {
        $this->jetonVerification = null;

        return $this;
    }

    public function genererJetonVerification(): void
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $this->jetonVerification = '';

        for ($i = 0; $i < 12; ++$i) {
            $this->jetonVerification .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }
    }

    public function estVerifieCourriel(): bool
    {
        return $this->estVerifieCourriel;
    }

    public function setVerifieCourriel(): static
    {
        $this->jetonVerification = null;
        $this->estVerifieCourriel = true;

        return $this;
    }

    public function getAdresse(): ?Adresse
    {
        return $this->adresse;
    }

    public function setAdresse(?Adresse $adresse): Requerant
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getIsPersonneMorale(): ?bool
    {
        return $this->isPersonneMorale && null !== $this->personneMorale;
    }

    public function setIsPersonneMorale(bool $isPersonneMorale): self
    {
        $this->isPersonneMorale = $isPersonneMorale;

        return $this;
    }

    public function getPersonnePhysique(): ?PersonnePhysique
    {
        return $this->personnePhysique;
    }

    public function setPersonnePhysique(PersonnePhysique $personnePhysique): self
    {
        $this->personnePhysique = $personnePhysique;

        return $this;
    }

    #[Groups('agent:detail')]
    #[SerializedName('civilite')]
    public function getCivilite(): string
    {
        return $this->personnePhysique->getCivilite()->value;
    }

    public function estFeminin(): bool
    {
        return $this->personnePhysique->getCivilite()->estFeminin();
    }

    #[Groups('agent:detail')]
    #[SerializedName('nom')]
    public function getNom(): string
    {
        return $this->personnePhysique->getNom();
    }

    #[Groups('agent:detail')]
    #[SerializedName('prenoms')]
    public function getPrenoms(): array
    {
        return [$this->personnePhysique->getPrenom1(), $this->personnePhysique->getPrenom2(), $this->personnePhysique->getPrenom3()];
    }

    #[Groups('agent:detail')]
    #[SerializedName('nomNaissance')]
    public function getNomNaissance(): ?string
    {
        return $this->personnePhysique->getNomNaissance();
    }

    #[Groups('agent:detail')]
    #[SerializedName('telephone')]
    public function getTelephone(): ?string
    {
        return $this->personnePhysique->getTelephone();
    }

    #[Groups('agent:detail')]
    #[SerializedName('dateNaissance')]
    public function getDateNaissance(): ?int
    {
        return DateConvertisseur::enMillisecondes($this->personnePhysique->getDateNaissance());
    }

    #[Groups('agent:detail')]
    #[SerializedName('communeNaissance')]
    public function getCommuneNaissance(): ?string
    {
        return $this->personnePhysique->getCommuneNaissance();
    }

    #[Groups('agent:detail')]
    #[SerializedName('paysNaissance')]
    public function getPaysNaissance(): ?string
    {
        return $this->personnePhysique->getPaysNaissance()?->getNom();
    }

    public function getPersonneMorale(): ?PersonneMorale
    {
        return $this->personneMorale;
    }

    public function setPersonneMorale(?PersonneMorale $personneMorale): Requerant
    {
        $this->personneMorale = $personneMorale;

        return $this;
    }

    #[Groups('agent:detail')]
    #[SerializedName('raisonSociale')]
    public function getRaisonSociale(): ?string
    {
        return $this->isPersonneMorale ? $this->personneMorale?->getRaisonSociale() : null;
    }

    #[Groups('agent:detail')]
    #[SerializedName('siren')]
    public function getSiren(): ?string
    {
        return $this->isPersonneMorale ? $this->getPersonneMorale()?->getRaisonSociale() : null;
    }

    public function getNomCourant(bool $civilite = false, bool $capital = false): string
    {
        return $this->getPersonnePhysique()?->getNomCourant($civilite, $capital);
    }

    /**
     * Affiche l'appellation officielle du requérant, soit:
     * - "Monsieur DUPONT Jean" pour un particulier
     * - "la société ACME représentée par Madame DUPONT, née MARTIN, Jeanne" pour un particulier
     */
    public function getNomComplet(): string
    {
        return ($this->isPersonneMorale ?
            "la société {$this->personneMorale->getRaisonSociale()} représentée par " : '').$this->personnePhysique->getNomComplet();
    }

    public function getDernierDossier(): ?BrisPorte
    {
        return $this->dossiers->isEmpty() ? null : $this->dossiers->last();
    }

    /**
     * @return Collection|BrisPorte[]
     */
    public function getDossiers(): Collection|array
    {
        return $this->dossiers;
    }

    public function __toString(): string
    {
        return $this->getPersonnePhysique()->__toString();
    }
}
