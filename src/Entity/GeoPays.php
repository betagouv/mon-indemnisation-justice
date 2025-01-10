<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\GeoPaysRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Table(name: 'geo_pays')]
#[ORM\Entity(repositoryClass: GeoPaysRepository::class)]
#[ApiResource(uriTemplate: '/geo-pays/{code}')]
class GeoPays
{
    #[ORM\Id]
    #[ORM\Column(length: 3)]
    #[Groups(['dossier:lecture'])]
    protected string $code;

    #[ORM\Column]
    #[Groups(['dossier:lecture'])]
    protected string $nom;

    #[ORM\Column(type: 'boolean')]
    protected bool $estFrance = false;


    public function getCode(): string
    {
        return $this->code;
    }

    public function getNom(): string
    {
        return $this->nom;
    }
}
