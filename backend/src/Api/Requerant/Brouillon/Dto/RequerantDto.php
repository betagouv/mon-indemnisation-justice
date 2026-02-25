<?php

namespace MonIndemnisationJustice\Api\Requerant\Brouillon\Dto;

use MonIndemnisationJustice\Entity\Civilite;
use MonIndemnisationJustice\Entity\Requerant;
use Symfony\Component\ObjectMapper\Attribute\Map;

#[Map(source: Requerant::class)]
#[Map(target: Requerant::class)]
class RequerantDto
{
    public ?bool $estPersonneMorale;
    // public ?TypePersonneMorale $typePersonneMorale;
    public ?string $raisonSociale;
    public ?string $siren;
    /*
    public bool $estRepresentantLegal;
    public Civilite $civiliteRepresentantLegal;
    public string $nomRepresentantLegal?: ;
    public string $nomNaissanceRepresentantLegal?: ;
    public string $prenomRepresentantLegal?: ;
    public string $courrielRepresentantLegal?: ;
    public string $telephoneRepresentantLegal?: ;
    */
    public Civilite $civilite;
    public string $nom;
    public string $nomNaissance;
    public string $prenom;
    public string $courriel;
    public string $telephone;
    public ?AdresseDto $adresse;
    public ?\DateTimeImmutable $dateNaissance;
    public ?PaysDto $paysNaissance;
}
