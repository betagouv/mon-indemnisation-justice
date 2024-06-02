<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\CiviliteRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
  cacheHeaders: [
    'max_age' => 6000,
    'shared_max_age' => 12000,
    'vary' => ['Authorization', 'Accept-Language']
  ],
  operations:[
    new Get(name: '_api_civilite_get'),
    new GetCollection(name: '_api_civilite_get_collection'),
  ]
  )]
#[ORM\Entity(repositoryClass: CiviliteRepository::class)]
class Civilite
{
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
