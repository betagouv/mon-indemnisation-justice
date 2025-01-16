<?php

namespace MonIndemnisationJustice\Entity;

use MonIndemnisationJustice\Repository\AgentRepository;
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
    // Le role ROLE_AGENT est donné à chaque agent de la fonction publique
    public const ROLE_AGENT = 'ROLE_AGENT';
    // Le role ROLE_AGENT_REDACTEUR est donné au rédacteur du pôle précontentieux
    public const ROLE_AGENT_REDACTEUR = 'ROLE_AGENT_REDACTEUR';
    // Le rôle ROLE_AGENT_GESTION_PERSONNEL peut ajouter ou activer / désactiver un compte rédacteur
    public const ROLE_AGENT_GESTION_PERSONNEL = 'ROLE_AGENT_GESTION_PERSONNEL';

    // Le rôle ROLE_AGENT_VALIDATEUR est donné à la cheffe du pôle précontentieux : elle valide la décision prise sur
    // un dossier d'indemnisation et signe la lettre qui l'officialise.
    public const ROLE_AGENT_VALIDATEUR = 'ROLE_AGENT_VALIDATEUR';

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
     * @var string[] la liste des rôles assignée à l'agent
     */
    #[ORM\Column(type: 'simple_array')]
    protected array $roles = [];
    #[ORM\Column(nullable: true)]
    protected ?string $motDePasse = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    protected ?\DateTimeInterface $dateChangementMDP = null;

    #[ORM\Column(type: 'string', length: 12, nullable: true)]
    protected ?string $jetonVerification;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    protected bool $estActif = true;

    public function __construct()
    {
    }

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
        $roles = [];
        if ($this->hasRole(self::ROLE_AGENT_GESTION_PERSONNEL)) {
            $roles[] = 'gestionnaire';
        }

        if ($this->hasRole(self::ROLE_AGENT_VALIDATEUR)) {
            $roles[] = 'validateur';
        }
        if ($this->hasRole(self::ROLE_AGENT_REDACTEUR)) {
            $roles[] = 'rédacteur';
        }

        return ucfirst(implode(', ', $roles));
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
                self::ROLE_AGENT,
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

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

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

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        return array_unique($this->roles);
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
        return $this->motDePasse;
    }

    public function setPassword(string $motDePasse): self
    {
        $this->motDePasse = $motDePasse;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        $this->motDePasse = null;
    }

    public function getUsername(): ?string
    {
        return $this->email;
    }

    public function getDateChangementMDP(): ?\DateTimeInterface
    {
        return $this->dateChangementMDP;
    }

    public function setDateChangementMDP(?\DateTimeInterface $dateChangementMDP): self
    {
        $this->dateChangementMDP = $dateChangementMDP;

        return $this;
    }

    public function getJetonVerification(): ?string
    {
        return $this->jetonVerification;
    }

    public function genererJetonVerification(): void
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $this->jetonVerification = '';

        for ($i = 0; $i < 12; ++$i) {
            $this->jetonVerification .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }
    }

    public function supprimerJetonVerification(): void
    {
        $this->jetonVerification = null;
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
