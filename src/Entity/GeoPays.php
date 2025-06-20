<?php

namespace MonIndemnisationJustice\Entity;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use MonIndemnisationJustice\Repository\GeoPaysRepository;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Table(name: 'geo_pays')]
#[ORM\Entity(repositoryClass: GeoPaysRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Index(name: 'idx_pays_code_insee', columns: ['code_insee'])]
#[ApiResource(uriTemplate: '/geo-pays/{code}', operations: [])]
class GeoPays extends GeoDataEntity
{
    public const CODE_INSEE_FRANCE = '99100';

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

    public function estFrance(): bool
    {
        return self::CODE_INSEE_FRANCE === $this->codeInsee;
    }

    public function setCodeInsee(?string $codeInsee): GeoPays
    {
        $this->codeInsee = $codeInsee;

        return $this;
    }
}
