<?php

namespace MonIndemnisationJustice\Entity;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use MonIndemnisationJustice\Repository\GeoPaysRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Table(name: 'geo_pays')]
#[ORM\Entity(repositoryClass: GeoPaysRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['codeInsee'], message: 'Ce code INSEE est déjà attribué à un pays')]
#[ApiResource(uriTemplate: '/geo-pays/{code}')]
class GeoPays extends GeoDataEntity
{
    #[ORM\Id]
    #[ORM\Column(length: 3)]
    #[Groups(['dossier:lecture'])]
    protected string $code;

    #[ORM\Column]
    #[Groups(['dossier:lecture'])]
    protected string $nom;

    #[ORM\Column(length: 5, nullable: true)]
    protected ?string $codeInsee;

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): GeoPays
    {
        $this->code = $code;

        return $this;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): GeoPays
    {
        $this->nom = $nom;

        return $this;
    }

    public function getCodeInsee(): ?string
    {
        return $this->codeInsee;
    }

    public function setCodeInsee(?string $codeInsee): GeoPays
    {
        $this->codeInsee = $codeInsee;

        return $this;
    }
}
