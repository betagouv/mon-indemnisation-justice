<?php

namespace App\Entity;

use DateTimeInterface;
use App\Repository\AgentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: AgentRepository::class)]
#[ORM\Table(name: 'agents')]
#[ORM\UniqueConstraint(name: 'uniq_agent_email', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'Cet adresse courriel est déjà attribuée à un agent')]
class Agent implements UserInterface, PasswordAuthenticatedUserInterface
{
    // Le role ROLE_REDACTEUR_PRECONTENTIEUX est donné au rédacteur du pôle précontentieux
    public const ROLE_AGENT_REDACTEUR = 'ROLE_REDACTEUR_PRECONTENTIEUX';
    // Le rôle ROLE_ADMIN_FONC peut ajouter ou activer / désactiver un compte rédacteur
    public const ROLE_AGENT_GESTION_PERSONNEL = 'ROLE_ADMIN_FONC';

    // Le rôle ROLE_CHEF_PRECONTENTIEUX est donné à la cheffe du pôle précontentieux : elle valide la décision prise sur
    // un dossier d'indemnisation et signe la lettre qui l'officialise
    public const ROLE_AGENT_VALIDATEUR = 'ROLE_CHEF_PRECONTENTIEUX';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    protected ?int $id;

    #[ORM\Column(length: 180)]
    protected string $email;

    #[ORM\Column(length: 50)]
    protected string $nom;

    #[ORM\Column(length: 30)]
    protected string $prenom;

    /**
     * @var string[] la liste des rôles assignées à l'agent
     */
    #[ORM\Column(type: 'simple_array')]
    protected array $roles = [];
    #[ORM\Column]
    private string $motDePasse;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTimeInterface $dateChangementMDP = null;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $estActif = true;

    public function __construct()
    {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPlaintextRole(): string
    {
        if($this->hasRole(self::ROLE_AGENT_VALIDATEUR))
          return self::ROLE_AGENT_VALIDATEUR;
        if($this->hasRole(self::ROLE_AGENT_REDACTEUR))
          return self::ROLE_AGENT_REDACTEUR;

        return '';
    }
    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRoles());
    }

    public function addRole(string $role): self
    {
        $roles = $this->getRoles();
        if (
            !in_array($role, $roles)
            && in_array($role, [
                self::ROLE_AGENT_REDACTEUR,
                self::ROLE_AGENT_GESTION_PERSONNEL,
                self::ROLE_AGENT_VALIDATEUR,
            ])
        ) {
            $roles[] = $role;
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
        return $this->email;
    }

    public function isAdminFonc(): bool
    {
        return $this->hasRole(self::ROLE_AGENT_GESTION_PERSONNEL);
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        return array_unique($this->roles, [self::ROLE_AGENT_REDACTEUR]);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): self
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

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        $this->password = null;
    }

    public function getUsername(): ?string
    {
        return $this->email;
    }

    public function getDateChangementMDP(): ?DateTimeInterface
    {
        return $this->dateChangementMDP;
    }

    public function setDateChangementMDP(?DateTimeInterface $dateChangementMDP): self
    {
        $this->dateChangementMDP = $dateChangementMDP;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->estActif;
    }

    public function setActive(bool $active): static
    {
        $this->estActif = $active;

        return $this;
    }

    public function getNomComplet(): ?string
    {
        return "$this->prenom $this->nom";
    }

    public function __toString(): string
    {
        return $this->getNomComplet();
    }
}
