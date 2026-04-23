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
        public Civilite $civilite,
        public string $nom,
        public ?string $nomNaissance,
        public array $prenoms,
        public string $courriel,
        public ?string $telephone,
        #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'])]
        public ?\DateTime $dateNaissance,
        public ?string $communeNaissance,
        #[Ignore]
        public ?string $villeNaissance,
        public ?string $paysNaissance,
        public ?string $raisonSociale,
        public ?string $siren,
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
            paysNaissance: $personnePhysique?->getPaysNaissance()->getNom(),
            raisonSociale: $personneMorale?->getRaisonSociale(),
            siren: $personneMorale?->getSirenSiret(),
        );
    }
}
