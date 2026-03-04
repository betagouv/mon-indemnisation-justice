<?php

namespace MonIndemnisationJustice\Api\Requerant\Brouillon\Dto;

class PersonnePhysiqueDto
{
    public ?PersonneDto $personne;
    public ?AdresseDto $adresse;
    public ?\DateTimeImmutable $dateNaissance;
    public ?PaysDto $paysNaissance;
    public ?CommuneDto $communeNaissance;
    public ?string $villeNaissance;
}
