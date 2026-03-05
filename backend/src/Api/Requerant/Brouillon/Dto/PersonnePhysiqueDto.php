<?php

namespace MonIndemnisationJustice\Api\Requerant\Brouillon\Dto;

use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

class PersonnePhysiqueDto
{
    public ?PersonneDto $personne;
    public ?AdresseDto $adresse;
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    public ?\DateTimeImmutable $dateNaissance;
    public ?PaysDto $paysNaissance;
    public ?CommuneDto $communeNaissance;
    public ?string $villeNaissance;
}
