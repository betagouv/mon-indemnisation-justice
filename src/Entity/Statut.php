<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Contracts\EntityInterface;
use App\Contracts\PrejudiceInterface;
use App\Entity\BrisPorte;
use App\Repository\StatutRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: StatutRepository::class)]
#[ApiResource]
class Statut implements EntityInterface
{
    const CODE_EN_COURS_DE_CONSTITUTION = 'EN_COURS_DE_CONSTITUTION';
    const CODE_CONSTITUE                = 'CONSTITUE';
    const CODE_RENVOI_EN_CONSTITUTION   = 'RENVOI_EN_CONSTITUTION';
    const CODE_VALIDE                   = 'VALIDE';
    const CODE_REJETE                   = 'REJETE';
    const CODE_SIGNATURE_VALIDEE        = 'SIGNATURE_VALIDEE';
    const CODE_SIGNATURE_REJETEE        = 'SIGNATURE_REJETEE';
    const CODE_ACCORD_OFFRE             = 'ACCORD_OFFRE';
    const CODE_REFUS_OFFRE              = 'REFUS_OFFRE';

    #[Groups('prejudice:read')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups('prejudice:read')]
    #[ORM\ManyToOne(inversedBy: 'statuts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?BrisPorte $prejudice = null;

    #[Groups('prejudice:read')]
    #[ORM\Column(length: 40)]
    private ?string $code = null;

    #[Groups('prejudice:read')]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $emetteur = null;

    public function __construct()
    {
      $this->date = new \DateTime();
      $this->code = self::CODE_EN_COURS_DE_CONSTITUTION;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrejudice(): ?PrejudiceInterface
    {
        return $this->prejudice;
    }

    public function setPrejudice(?PrejudiceInterface $prejudice): static
    {
        $this->prejudice = $prejudice;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getEmetteur(): ?User
    {
        return $this->emetteur;
    }

    public function setEmetteur(?User $emetteur): static
    {
        $this->emetteur = $emetteur;

        return $this;
    }
}
