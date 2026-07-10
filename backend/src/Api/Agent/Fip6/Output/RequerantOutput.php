<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Output;

use MonIndemnisationJustice\Entity\Civilite;
use MonIndemnisationJustice\Entity\PersonneMorale;
use MonIndemnisationJustice\Entity\PersonnePhysique;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

class RequerantOutput
{
    public function __construct(
        public ?Civilite $civilite = null,
        public ?string $nom = null,
        public ?string $nomNaissance = null,
        public array $prenoms = [],
        public ?string $courriel = null,
        public ?string $telephone = null,
        #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'])]
        public ?\DateTimeImmutable $dateNaissance,
        public ?string $communeNaissance,
        #[Ignore]
        public ?string $villeNaissance,
        public ?string $paysNaissance,
        public ?string $raisonSociale,
        public ?string $siren,
        public ?string $typePersonneMorale,
    ) {
    }

    public static function depuisRequerant(PersonnePhysique|PersonneMorale $requerant): self
    {

        $personnePhysique = $requerant instanceof PersonnePhysique ? $requerant : null;
        $personneMorale = $requerant instanceof PersonneMorale ? $requerant : null;
        $personne = $requerant instanceof PersonneMorale ? $requerant->getRepresentantLegal() : $requerant->getPersonne();

        return new self(
            civilite: $personne->getCivilite(),
            nom: $personne->getNom(),
            nomNaissance: $personne->getNomNaissance(),
            prenoms: array_merge([$personne->getPrenom()], $requerant instanceof PersonnePhysique ? [$requerant->getPrenom2(), $requerant->getPrenom3()] : []),
            courriel: $personne->getCourriel(),
            telephone: $personne->getTelephone(),
            dateNaissance: $personnePhysique?->getDateNaissance(),
            communeNaissance: $personnePhysique?->getCodePostalNaissance()?->getCommune()->getNom(),
            villeNaissance: $personnePhysique?->getVilleNaissance(),
            paysNaissance: $personnePhysique?->getPaysNaissance()?->getNom(),
            raisonSociale: $personneMorale?->getRaisonSociale(),
            siren: $personneMorale?->getSirenSiret(),
            typePersonneMorale: $personneMorale?->getType()->value,
        );
    }
}
