<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\GetCollection;
use App\Contracts\PrejudiceInterface;
use App\Repository\BrisPorteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
  operations:[
    new Get(),
    new GetCollection(),
    new Put(),
  ]
)]
#[ORM\Entity(repositoryClass: BrisPorteRepository::class)]
class BrisPorte implements PrejudiceInterface
{
    use PrejudiceTrait;

    #[Groups('prejudice:read')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups('prejudice:read')]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numeroPV = null;

    #[Groups('prejudice:read')]
    #[ORM\ManyToOne(inversedBy: 'brisPortes')]
    private ?Adresse $adresse = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroPV(): ?string
    {
        return $this->numeroPV;
    }

    public function setNumeroPV(?string $numeroPV): static
    {
        $this->numeroPV = $numeroPV;

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
}
