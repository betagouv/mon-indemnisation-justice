<?php

namespace MonIndemnisationJustice\Entity;

use Doctrine\ORM\Mapping as ORM;
use MonIndemnisationJustice\Repository\AgentRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: AgentRepository::class)]
#[ORM\Table(name: 'agents')]
#[ORM\UniqueConstraint(name: 'uniq_agent_identifiant', fields: ['identifiant'])]
#[UniqueEntity(fields: ['identifiant'], message: 'Cet identifiant correspond à un autre agent')]
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

    public const ROLE_FORCES_DE_L_ORDRE = 'FORCES_DE_L_ORDRE';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    protected ?int $id;

    #[ORM\Column(nullable: false)]
    protected string $identifiant;

    #[ORM\Column(nullable: false)]
    protected string $uid;

    /**
     * Correspond au champs `idp_id` de ProConnect.
     *
     * La liste des organisations est définie icihttps://grist.numerique.gouv.fr/o/docs/3kQ829mp7bTy/AgentConnect-Configuration-des-Fournisseurs-dIdentite
     */
    #[ORM\Column(nullable: false)]
    protected string $fournisseurIdentite;

    #[ORM\Column(length: 180)]
    protected string $email;

    #[ORM\Column(length: 50)]
    protected string $nom;

    #[ORM\Column(length: 30)]
    protected string $prenom;

    #[ORM\Column(type: 'string', nullable: true, enumType: CategorieAgent::class)]
    protected ?CategorieAgent $categorieAgent;

    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $donnesAuthentification;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    protected bool $estValide = false;

    /**
     * @var string[] la liste des rôles assignée à l'agent
     */
    #[ORM\Column(type: 'simple_array')]
    protected array $roles = [];

    public function __construct()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdentifiant(): string
    {
        return $this->identifiant;
    }

    public function setIdentifiant(string $identifiant): Agent
    {
        $this->identifiant = $identifiant;

        return $this;
    }

    public function getUid(): string
    {
        return $this->uid;
    }

    public function setUid(string $uid): Agent
    {
        $this->uid = $uid;

        return $this;
    }

    public function setFournisseurIdentite(string $fournisseurIdentite): Agent
    {
        $this->fournisseurIdentite = $fournisseurIdentite;

        return $this;
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
        return $this->identifiant;
    }

    public function getCategorieAgent(): ?CategorieAgent
    {
        return $this->categorieAgent;
    }

    public function setCategorieAgent(?CategorieAgent $categorieAgent): Agent
    {
        $this->categorieAgent = $categorieAgent;

        return $this;
    }

    public function estValide(): bool
    {
        return $this->estValide;
    }

    public function setValide(bool $estValide): Agent
    {
        $this->estValide = $estValide;

        return $this;
    }

    public function setDonnesAuthentification(array|string|null $donnesAuthentification): Agent
    {
        if (null !== $donnesAuthentification) {
            $this->donnesAuthentification = (is_array($donnesAuthentification) ? (json_encode($donnesAuthentification) ?? '') : $donnesAuthentification);
        }

        return $this;
    }

    public function getPassword(): ?string
    {
        return null;
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
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
    }

    public function getUsername(): ?string
    {
        return $this->email;
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
