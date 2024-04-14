<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
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
    new Get(),
    new Patch(
      name: '_api_user_patch'
      #,
      #security: "is_granted('ROLE_ADMIN') or object.id == user.id"
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

    #[Groups('user:read')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups('user:read')]
    #[ORM\Column(length: 180)]
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

    #[Groups('user:read')]
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
    #[ORM\ManyToOne]
    private ?Civilite $civilite = null;

    #[Groups('user:read')]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[Groups('user:read')]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prenom1 = null;

    #[Groups('user:read')]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prenom2 = null;

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

    #[Groups('user:read')]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prenom3 = null;

    #[Groups('user:read')]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telephone = null;

    #[Groups('user:read')]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $portable = null;

    #[Groups('user:read')]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $communeNaissance = null;

    #[Groups('user:read')]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $paysNaissance = null;

    #[Groups('user:read')]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $dateNaissance = null;

    #[Groups('user:read')]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sirenSiret = null;

    #[Groups('user:read')]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $raisonSociale = null;

    #[Groups('user:read')]
    #[ORM\Column(options: ['default' => false])]
    private ?bool $isPersonneMorale = null;

    #[Groups('user:read')]
    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Adresse $adresse = null;

    #[Groups('user:read')]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomNaissance = null;

    public function __construct()
    {
        $this->adresse = new Adresse();
        $this->brisPortes = new ArrayCollection();
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

    public function getCivilite(): ?Civilite
    {
        return $this->civilite;
    }

    public function setCivilite(?Civilite $civilite): static
    {
        $this->civilite = $civilite;

        return $this;
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

    public function getPrenom3(): ?string
    {
        return $this->prenom3;
    }

    public function setPrenom3(?string $prenom3): static
    {
        $this->prenom3 = $prenom3;

        return $this;
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

    public function getPortable(): ?string
    {
        return $this->portable;
    }

    public function setPortable(?string $portable): static
    {
        $this->portable = $portable;

        return $this;
    }

    public function getCommuneNaissance(): ?string
    {
        return $this->communeNaissance;
    }

    public function setCommuneNaissance(?string $communeNaissance): static
    {
        $this->communeNaissance = $communeNaissance;

        return $this;
    }

    public function getPaysNaissance(): ?string
    {
        return $this->paysNaissance;
    }

    public function setPaysNaissance(?string $paysNaissance): static
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

    public function getSirenSiret(): ?string
    {
        return $this->sirenSiret;
    }

    public function setSirenSiret(?string $sirenSiret): static
    {
        $this->sirenSiret = $sirenSiret;

        return $this;
    }

    public function getRaisonSociale(): ?string
    {
        return $this->raisonSociale;
    }

    public function setRaisonSociale(?string $raisonSociale): static
    {
        $this->raisonSociale = $raisonSociale;

        return $this;
    }

    public function isPersonneMorale(): ?bool
    {
        return $this->isPersonneMorale;
    }

    public function setPersonneMorale(bool $isPersonneMorale): static
    {
        $this->isPersonneMorale = $isPersonneMorale;

        return $this;
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

    public function getNomNaissance(): ?string
    {
        return $this->nomNaissance;
    }

    public function setNomNaissance(?string $nomNaissance): static
    {
        $this->nomNaissance = $nomNaissance;

        return $this;
    }
}
