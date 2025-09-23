<?php

namespace MonIndemnisationJustice\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use MonIndemnisationJustice\Repository\AgentRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Entity(repositoryClass: AgentRepository::class)]
#[ORM\Table(name: 'agents')]
#[ORM\UniqueConstraint(name: 'uniq_agent_identifiant', fields: ['identifiant'])]
#[ORM\UniqueConstraint(name: 'uniq_agent_email', fields: ['email'])]
#[UniqueEntity(fields: ['identifiant'])]
class Agent implements UserInterface
{
    // Le role ROLE_AGENT est donné à chaque agent de la fonction publique
    public const ROLE_AGENT = 'ROLE_AGENT';
    public const ROLE_AGENT_BETAGOUV = 'ROLE_AGENT_BETAGOUV';
    // Le role ROLE_AGENT_DOSSIER permet de chercher et consulter un dossier
    public const ROLE_AGENT_DOSSIER = 'ROLE_AGENT_DOSSIER';

    // Le role ROLE_AGENT_REDACTEUR est donné au rédacteur du pôle précontentieux
    public const ROLE_AGENT_REDACTEUR = 'ROLE_AGENT_REDACTEUR';
    // Le rôle ROLE_AGENT_GESTION_PERSONNEL peut ajouter ou activer / désactiver un compte rédacteur
    public const ROLE_AGENT_GESTION_PERSONNEL = 'ROLE_AGENT_GESTION_PERSONNEL';

    // Le rôle ROLE_AGENT_VALIDATEUR est donné à la cheffe du pôle précontentieux : elle valide la décision prise sur
    // un dossier d'indemnisation et signe la lettre qui l'officialise.
    public const ROLE_AGENT_VALIDATEUR = 'ROLE_AGENT_VALIDATEUR';

    public const ROLE_AGENT_ATTRIBUTEUR = 'ROLE_AGENT_ATTRIBUTEUR';

    // Le rôle ROLE_AGENT_LIAISON_BUDGET est attribué à l'agent en charge de la liaison avec le bureau du budget
    // (envoi de l'arrêté de paiement et autres documents légaux ainsi que notification des versements effectués).
    public const ROLE_AGENT_LIAISON_BUDGET = 'ROLE_AGENT_LIAISON_BUDGET';

    public const ROLE_AGENT_BUREAU_BUDGET = 'ROLE_AGENT_BUREAU_BUDGET';

    public const ROLE_AGENT_FORCES_DE_L_ORDRE = 'ROLE_AGENT_FORCES_DE_L_ORDRE';

    #[Groups('agent:resume')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    protected ?int $id;

    #[ORM\Column(nullable: false)]
    protected string $identifiant;

    #[ORM\Column(nullable: false)]
    protected string $uid;

    #[ORM\Column(length: 180)]
    protected string $email;

    #[ORM\Column(length: 50)]
    protected string $nom;

    #[ORM\Column(length: 30)]
    protected string $prenom;

    #[ORM\Column(type: 'string', nullable: true, enumType: Administration::class)]
    #[Ignore]
    protected ?Administration $administration = null;

    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $donnesAuthentification;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    protected bool $estValide = false;

    /**
     * @var string[] la liste des rôles assignée à l'agent
     */
    #[ORM\Column(type: 'simple_array')]
    protected array $roles = [];

    #[ORM\OneToMany(targetEntity: BrisPorte::class, mappedBy: 'redacteur', cascade: ['detach'])]
    #[ORM\OrderBy(['dateCreation' => 'ASC'])]
    #[Ignore]
    /** @var Collection<BrisPorte> */
    protected Collection $dossiers;

    /**
     * Correspond à la propriété `idp_id` de ProConnect.
     *
     * La liste des organisations est définie ici https://grist.numerique.gouv.fr/o/docs/3kQ829mp7bTy/AgentConnect-Configuration-des-Fournisseurs-dIdentite
     */
    #[ORM\ManyToOne(targetEntity: FournisseurIdentiteAgent::class)]
    #[ORM\JoinColumn(name: 'fournisseur_identite_uid', referencedColumnName: 'uid')]
    protected ?FournisseurIdentiteAgent $fournisseurIdentite = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?\DateTimeInterface $dateCreation = null;

    public function __toString(): string
    {
        return $this->getNomComplet();
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

    public function getFournisseurIdentite(): FournisseurIdentiteAgent
    {
        return $this->fournisseurIdentite;
    }

    public function setFournisseurIdentite(?FournisseurIdentiteAgent $fournisseurIdentite): Agent
    {
        if (null !== $fournisseurIdentite) {
            $this->fournisseurIdentite = $fournisseurIdentite;

            if ($this->fournisseurIdentite->getAdministration()) {
                $this->setAdministration($this->fournisseurIdentite->getAdministration());
            }
        }

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

    public function getRolePrimaire(): ?string
    {
        if (null === $this->administration || !$this->estValide) {
            return null;
        }

        if (Administration::MINISTERE_JUSTICE !== $this->administration) {
            return "Forces de l'ordre";
        }

        if ($this->hasRole(self::ROLE_AGENT_BUREAU_BUDGET)) {
            return 'Agent du bureau du budget';
        }

        if ($this->hasRole(self::ROLE_AGENT_VALIDATEUR)) {
            return 'Agent validateur';
        }

        if ($this->hasRole(self::ROLE_AGENT_ATTRIBUTEUR)) {
            return 'Agent attributeur';
        }

        if ($this->hasRole(self::ROLE_AGENT_GESTION_PERSONNEL)) {
            return 'Agent gestion du personnel';
        }

        return 'rédacteur';
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRoles());
    }

    /**
     * Indique si l'agent possède *au moins un* des rôles.
     *
     * @param string[] $roles
     */
    public function aRole(...$roles): bool
    {
        return count(array_intersect($roles, $this->getRoles())) > 0;
    }

    public function addRole(string $role): self
    {
        $roles = $this->getRoles();
        if (
            !in_array($role, $roles)
            && str_starts_with($role, 'ROLE_AGENT')
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

    public function nbDossiersAInstruire(): int
    {
        return count($this->getDossiersAInstruire());
    }

    /**
     * @return BrisPorte[]
     */
    public function getDossiersAInstruire(): array
    {
        return $this->hasRole(Agent::ROLE_AGENT_REDACTEUR) ? $this->dossiers->filter(fn (BrisPorte $dossier) => in_array($dossier->getEtatDossier()->getEtat(), [EtatDossierType::DOSSIER_A_INSTRUIRE, EtatDossierType::DOSSIER_EN_INSTRUCTION]))->toArray() : [];
    }

    public function nbDossiersAVerifier(): int
    {
        return count($this->getDossiersAVerifier());
    }

    /**
     * @return BrisPorte[]
     */
    public function getDossiersAVerifier(): array
    {
        return $this->hasRole(Agent::ROLE_AGENT_REDACTEUR) ? $this->dossiers->filter(fn (BrisPorte $dossier) => EtatDossierType::DOSSIER_OK_A_VERIFIER === $dossier->getEtatDossier()->getEtat())->toArray() : [];
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return $this->getNomComplet(true);
    }

    public function getAdministration(): ?Administration
    {
        return $this->administration;
    }

    public function setAdministration(?Administration $administration): Agent
    {
        if (null !== $administration) {
            $this->administration = $administration;

            foreach ($this->administration->getRolesAutomatiques() as $role) {
                $this->addRole($role);
            }

            $this->setValide($this->administration->estAutoValide());
        }

        return $this;
    }

    public function estValide(): bool
    {
        return $this->estValide;
    }

    public function setValide(bool $estValide = true): Agent
    {
        if (!$this->estValide) {
            $this->estValide = $estValide;
        }

        return $this;
    }

    public function estRedacteur(): bool
    {
        return $this->hasRole(Agent::ROLE_AGENT_REDACTEUR);
    }

    public function instruit(BrisPorte $dossier): bool
    {
        return $this->estRedacteur() && $dossier->getRedacteur() === $this;
    }

    public function getDonnesAuthentification(): ?array
    {
        return $this->donnesAuthentification ? json_decode($this->donnesAuthentification, true) : null;
    }

    public function setDonnesAuthentification(null|array|string $donnesAuthentification): Agent
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
     * @return list<string>
     *
     * @see UserInterface
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
    public function eraseCredentials(): void {}

    public function getUsername(): ?string
    {
        return $this->email;
    }

    public function getNomCourt(): ?string
    {
        return sprintf('%s. %s', $this->prenom[0], $this->nom);
    }

    #[Groups('agent:resume')]
    #[SerializedName('nom')]
    public function getNomComplet($capital = false): ?string
    {
        return sprintf('%s %s', $this->prenom, $capital ? strtoupper(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $this->nom)) : $this->nom);
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setCree(): static
    {
        $this->dateCreation = new \DateTime();

        return $this;
    }
}
