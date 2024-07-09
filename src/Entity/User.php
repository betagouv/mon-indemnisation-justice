<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use App\Contracts\EntityInterface;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
  operations:[
    new GetCollection(
      name: '_api_user_get_collection'
    ),
    new Get(
      name: '_api_user_get',
      normalizationContext: ["groups" => ["user:write"]]
    ),
    new Patch(
      name: '_api_user_patch'
      #,security: "is_granted('ROLE_REQUERANT')"
  )]
)]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface, EntityInterface
{
    const ROLE_ADMIN_FONC = 'ROLE_ADMIN_FONC';
    const ROLE_USER = 'ROLE_USER';
    const ROLE_REQUERANT = 'ROLE_REQUERANT';
    const ROLE_REDACTEUR_PRECONTENTIEUX = 'ROLE_REDACTEUR_PRECONTENTIEUX';
    const ROLE_CHEF_PRECONTENTIEUX = 'ROLE_CHEF_PRECONTENTIEUX';

    #[Groups(['user:read','prejudice:read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['user:read','prejudice:read'])]
    #[ORM\Column(length: 180)]
    #[ApiFilter(SearchFilter::class, strategy: 'exact')]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[Groups(['user:read','prejudice:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $username = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateChangementMDP = null;

    #[ORM\Column(type: 'boolean')]
    private $isVerified = false;

    #[Groups('user:read')]
    #[ORM\OneToMany(targetEntity: BrisPorte::class, mappedBy: 'requerant')]
    private Collection $brisPortes;

    #[Groups('user:read')]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mnemo = null;

    #[Groups('user:read')]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fonction = null;

    #[Groups('user:read')]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $titre = null;

    #[Groups('user:read')]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $grade = null;

    #[Groups(['user:read','prejudice:read','user:write'])]
    #[ORM\Column(options: ['default' => false])]
    private ?bool $isPersonneMorale = null;

    #[Groups(['user:read','prejudice:read','user:write'])]
    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Adresse $adresse = null;

    #[Groups(['user:read','prejudice:read','user:write'])]
    #[ORM\OneToOne(inversedBy: 'compte', cascade: ['persist', 'remove'])]
    private ?PersonnePhysique $personnePhysique = null;

    #[Groups(['user:read','prejudice:read','user:write'])]
    #[ORM\OneToOne(inversedBy: 'compte', cascade: ['persist', 'remove'])]
    private ?PersonneMorale $personneMorale = null;

    #[Groups(['user:read','prejudice:read','user:write'])]
    public readonly ?int $pId;

    #[Groups('user:read','user:write')]
    private $plaintextRole;

    #[Groups('user:read')]
    #[ORM\Column(options: ['default' => true])]
    private bool $active = true;

    /**
     * @var Collection<int, Tracking>
     */
    #[ORM\OneToMany(targetEntity: Tracking::class, mappedBy: 'account')]
    private Collection $trackings;

    public function __construct()
    {
        $this->personneMorale = new PersonneMorale();
        $this->personnePhysique = new PersonnePhysique();
        $this->isPersonneMorale = false;
        $this->adresse = new Adresse();
        $this->brisPortes = new ArrayCollection();
        $this->active = true;
        $this->trackings = new ArrayCollection();
    }

    public function getPId(): ?int
    {
      return $this->getId();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPersonnePhysiquePlaintext(): string
    {
        return (string)$this->getPersonnePhysique();
    }

    public function getPlaintextRole(): string
    {
        if($this->hasRole(self::ROLE_CHEF_PRECONTENTIEUX))
          return self::ROLE_CHEF_PRECONTENTIEUX;
        if($this->hasRole(self::ROLE_REDACTEUR_PRECONTENTIEUX))
          return self::ROLE_REDACTEUR_PRECONTENTIEUX;
        return '';
    }
    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRoles());
    }

    public function addRole(string $role): self
    {
        $roles = $this->getRoles();
        if(
          !in_array($role, $roles)
          &&
          in_array($role, [
            self::ROLE_USER,
            self::ROLE_REDACTEUR_PRECONTENTIEUX,
            self::ROLE_CHEF_PRECONTENTIEUX,
            self::ROLE_ADMIN_FONC,
            self::ROLE_REQUERANT,
          ])
        ) {
          $roles[]=$role;
          $this->setRoles($roles);
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

    public function isAdminFonc(): bool
    {
        $roles = $this->getRoles();
        return in_array(self::ROLE_ADMIN_FONC, $roles);
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
        return $this->username;
    }

    public function setUsername(?string $username): static
    {
        $this->username = $username;

        return $this;
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

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * @return Collection<int, BrisPorte>
     */
    public function getBrisPortes(): Collection
    {
        return $this->brisPortes;
    }

    public function addBrisPorte(BrisPorte $brisPorte): static
    {
        if (!$this->brisPortes->contains($brisPorte)) {
            $this->brisPortes->add($brisPorte);
            $brisPorte->setRequerant($this);
        }

        return $this;
    }

    public function removeBrisPorte(BrisPorte $brisPorte): static
    {
        if ($this->brisPortes->removeElement($brisPorte)) {
            // set the owning side to null (unless already changed)
            if ($brisPorte->getRequerant() === $this) {
                $brisPorte->setRequerant(null);
            }
        }

        return $this;
    }

    public function getMnemo(): ?string
    {
        return $this->mnemo;
    }

    public function setMnemo(?string $mnemo): static
    {
        $this->mnemo = $mnemo;

        return $this;
    }

    public function getFonction(): ?string
    {
        return $this->fonction;
    }

    public function setFonction(?string $fonction): static
    {
        $this->fonction = $fonction;

        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(?string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getGrade(): ?string
    {
        return $this->grade;
    }

    public function setGrade(?string $grade): static
    {
        $this->grade = $grade;

        return $this;
    }

    public function getIsPersonneMorale(): ?bool
    {
        return $this->isPersonneMorale;
    }

    public function setIsPersonneMorale(bool $isPersonneMorale): static
    {
      $this->isPersonneMorale = $isPersonneMorale;

      return $this;
    }

    public function isPersonneMorale(): ?bool
    {
        return $this->getIsPersonneMorale();
    }

    public function getAdresse(): ?Adresse
    {
        return $this->adresse;
    }

    public function setAdresse(?Adresse $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getPersonnePhysique(): ?PersonnePhysique
    {
        return $this->personnePhysique;
    }

    public function setPersonnePhysique(?PersonnePhysique $personnePhysique): static
    {
        $this->personnePhysique = $personnePhysique;

        return $this;
    }

    public function getPersonneMorale(): ?PersonneMorale
    {
        return $this->personneMorale;
    }

    public function setPersonneMorale(?PersonneMorale $personneMorale): static
    {
        $this->personneMorale= $personneMorale;

        return $this;
    }

    public function getActive(): bool
    {
        return $this->isActive();
    }

    public function isActive():bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getNomComplet(): ?string
    {
        $personnePhysique = $this->getPersonnePhysique();
        return $personnePhysique ? $personnePhysique->getNomComplet() : null;
    }

    /**
     * @return Collection<int, Tracking>
     */
    public function getTrackings(): Collection
    {
        return $this->trackings;
    }

    public function addTracking(Tracking $tracking): static
    {
        if (!$this->trackings->contains($tracking)) {
            $this->trackings->add($tracking);
            $tracking->setAccount($this);
        }

        return $this;
    }

    public function removeTracking(Tracking $tracking): static
    {
        if ($this->trackings->removeElement($tracking)) {
            // set the owning side to null (unless already changed)
            if ($tracking->getAccount() === $this) {
                $tracking->setAccount(null);
            }
        }

        return $this;
    }
}
