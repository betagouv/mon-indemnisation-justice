<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\QualiteRequerantRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QualiteRequerantRepository::class)]
#[ApiResource]
class QualiteRequerant
{
    const CODE_PROPRIETAIRE = '1';
    const CODE_LOCATAIRE    = '2';
    const CODE_HEBERGEANT   = '3';
    const CODE_AUTRE        = '4';
    
    use ReferentielTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
