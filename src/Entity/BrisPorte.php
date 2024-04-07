<?php

namespace App\Entity;

use App\Repository\BrisPorteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BrisPorteRepository::class)]
class BrisPorte
{
    use SinistreTrait;
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numeroPV = null;

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
}
