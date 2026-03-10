<?php

namespace MonIndemnisationJustice\Api\Requerant\Brouillon\Mapper;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Requerant\Brouillon\Dto\DossierDto;
use MonIndemnisationJustice\Entity\Adresse;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\DossierType;
use MonIndemnisationJustice\Entity\GeoPays;
use MonIndemnisationJustice\Entity\Personne;
use MonIndemnisationJustice\Entity\PersonneMorale;
use MonIndemnisationJustice\Entity\PersonnePhysique;
use MonIndemnisationJustice\Entity\Usager;
use Symfony\Component\ObjectMapper\TransformCallableInterface;

class DossierDtoMapper implements TransformCallableInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function __invoke(mixed $value, object $source, ?object $target): mixed
    {
        if ($source instanceof DossierDto) {
            return $this->versDossier($source);
        }

        if ($source instanceof Dossier) {
            return $this->depuisDossier($source);
        }
    }

    public function depuisDossier(Dossier $dossier): DossierDto
    {
        return new DossierDto();
    }

    public function versDossier(DossierDto $source): Dossier
    {
        return new Dossier()
            ->setUsager($this->em->getRepository(Usager::class)->find($source->usager))
            ->setType(DossierType::BRIS_PORTE)
            ->setBrisPorte(
                new BrisPorte()
                    ->setRapportAuLogement($source->rapportAuLogement)
                    ->setPrecisionRapportAuLogement($source->descriptionRapportAuLogement)
                    ->setDateOperation($source->dateOperation)
                    ->setDescriptionRequerant($source->description)
                    ->setPorteBlindee($source->estPorteBlindee)
                    ->setAdresse(
                        new Adresse()
                            ->setLigne1($source->adresse->ligne1)
                            ->setLigne2($source->adresse->ligne2)
                            ->setCodePostal($source->adresse->codePostal)
                            ->setLocalite($source->adresse->commune)
                    )
            )
            ->setRequerant(
                (null !== $source->estPersonneMorale ? $source->estPersonneMorale : null !== $source->personneMorale) ?
                    new PersonneMorale()
                        ->setRaisonSociale($source->personneMorale->raisonSociale)
                        ->setSirenSiret($source->personneMorale->siren)
                        ->setRepresentantLegal(
                            new Personne()
                                ->setCivilite($source->personneMorale->representantLegal->civilite)
                                ->setPrenom($source->personneMorale->representantLegal->prenom)
                                ->setNom($source->personneMorale->representantLegal->nom)
                                ->setNomNaissance($source->personneMorale->representantLegal->nomNaissance)
                                ->setCourriel($source->personneMorale->representantLegal->courriel)
                                ->setTelephone($source->personneMorale->representantLegal->telephone)
                        )
                    :
                    new PersonnePhysique()
                        ->setPersonne(
                            new Personne()
                                ->setCivilite($source->personnePhysique->personne->civilite)
                                ->setPrenom($source->personnePhysique->personne->prenom)
                                ->setNom($source->personnePhysique->personne->nom)
                                ->setNomNaissance($source->personnePhysique->personne->nomNaissance)
                                ->setCourriel($source->personnePhysique->personne->courriel)
                                ->setTelephone($source->personnePhysique->personne->telephone)
                        )
                        ->setDateNaissance($source->personnePhysique->dateNaissance)
                        ->setPaysNaissance(
                            $this->em->getRepository(GeoPays::class)->find($source->personnePhysique->paysNaissance->code)
                        )
                        ->setCommuneNaissance(
                            'FRA' === $source->personnePhysique->paysNaissance->code ?
                                $this->em->getRepository(GeoPays::class)->find($source->personnePhysique->communeNaissance->id) : null
                        )
                        ->setVilleNaissance('FRA' === $source->personnePhysique->paysNaissance->code ? null : $source->personnePhysique->villeNaissance)
                        ->setAdresse(
                            new Adresse()
                                ->setLigne1($source->personnePhysique->adresse->ligne1)
                                ->setLigne2($source->personnePhysique->adresse->ligne2)
                                ->setCodePostal($source->personnePhysique->adresse->codePostal)
                                ->setLocalite($source->personnePhysique->adresse->commune)
                        )
            );
    }
}
