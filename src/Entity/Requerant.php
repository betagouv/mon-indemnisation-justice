<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use App\Repository\RequerantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Attribute\Context;

#[ApiResource(
    operations: [
        new GetCollection(
            name: '_api_requerant_get_collection',
            security: "is_granted('ROLE_REQUERANT')"
        ),
        new Get(
            name: '_api_requerant_get',
            normalizationContext: ['groups' => ['user:write']],
            security: "is_granted('ROLE_REQUERANT')"
        ),
        new Patch(
            name: '_api_requerant_patch',
            security: "is_granted('ROLE_REQUERANT')"
        )]
)]
#[ORM\Entity(repositoryClass: RequerantRepository::class)]
#[ORM\Table(name: 'requerants')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class Requerant implements UserInterface, PasswordAuthenticatedUserInterface
{
    // Le rôle ROLE_REQUERANT est celui donné au porteur d'une requête d'indemnisation
    public const ROLE_REQUERANT = 'ROLE_REQUERANT';

    #[Groups(['user:read', 'prejudice:read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['user:read', 'prejudice:read'])]
    #[ORM\Column(length: 180)]
    #[ApiFilter(SearchFilter::class, strategy: 'exact')]
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

    #[Groups(['user:read', 'prejudice:read', 'user:write'])]
    #[ORM\Column(options: ['default' => false])]
    protected bool $isPersonneMorale = false;

    #[ORM\Column(type: 'json', nullable: true)]
    protected ?array $testEligibilite = null;

    #[Groups(['user:read', 'prejudice:read', 'user:write'])]
    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Adresse $adresse;

    #[Groups(['user:read', 'prejudice:read', 'user:write'])]
    #[ORM\OneToOne(inversedBy: 'compte', cascade: ['persist', 'remove'])]
    private ?PersonnePhysique $personnePhysique = null;

    #[Groups(['user:read', 'prejudice:read', 'user:write'])]
    #[ORM\OneToOne(inversedBy: 'compte', cascade: ['persist', 'remove'])]
    private ?PersonneMorale $personneMorale = null;

    #[ORM\OneToMany(targetEntity: BrisPorte::class, mappedBy: 'requerant', cascade: ['remove'])]
    protected Collection $brisPorte;

    public function __construct()
    {
        $this->personneMorale = new PersonneMorale();
        $this->personnePhysique = new PersonnePhysique();
        $this->adresse = new Adresse();
        $this->brisPorte = new ArrayCollection();
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

        return $this->isPersonneMorale;
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

    public function setPersonnePhysique(?PersonnePhysique $personnePhysique): self
    {
        $this->personnePhysique = $personnePhysique;
        return $this;
    }


    public function getPersonneMorale(): ?PersonneMorale
    {
        return $this->personneMorale;
    }

    public function getNomComplet(): ?string
    {
        return $this->getPersonnePhysique()?->getNomComplet() ?? null;
    }

    public function getTestEligibilite(): ?array
    {
        return $this->testEligibilite;
    }

    public function setTestEligibilite(?array $testEligibilite): self
    {
        $this->testEligibilite = $testEligibilite;
        return $this;
    }

    public function __toString(): string
    {
        return $this->getPersonnePhysique()->__toString();
    }


}
