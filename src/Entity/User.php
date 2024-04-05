<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    const ROLE_ADMIN_FONC = 'ROLE_ADMIN_FONC';
    const ROLE_USER = 'ROLE_USER';
    const ROLE_REDACTEUR_PRECONTENTIEUX = 'ROLE_REDACTEUR_PRECONTENTIEUX';
    const ROLE_CHEF_PRECONTENTIEUX = 'ROLE_CHEF_PRECONTENTIEUX';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

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

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $username = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateChangementMDP = null;

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
    public function getPassword(): string
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
}
