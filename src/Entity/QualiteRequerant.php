<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\QualiteRequerantRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QualiteRequerantRepository::class)]
#[ApiResource(
  cacheHeaders: [
    'max_age' => 6000,
    'shared_max_age' => 12000,
    'vary' => ['Authorization', 'Accept-Language']
  ],
  operations:[
    new Get(name: '_api_qualite_representant_get'),
    new GetCollection(name: '_api_qualite_representant_get_collection'),
  ]
  )]
class QualiteRequerant
{
    const CODE_PROPRIETAIRE = '1';
    const CODE_LOCATAIRE    = '2';
    const CODE_HEBERGEANT   = '3';
    const CODE_AUTRE        = '4';

    use ReferentielTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
