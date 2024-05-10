<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\CiviliteRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
  operations:[
    new Get(name: '_api_civilite_get'),
    new GetCollection(name: '_api_civilite_get_collection'),
  ]
  )]
#[ORM\Entity(repositoryClass: CiviliteRepository::class)]
class Civilite
{
    use ReferentielTrait;

    #[Groups(['user:read','prejudice:read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
