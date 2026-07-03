<?php

namespace MonIndemnisationJustice\Service\DataGouv;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\Administration;
use MonIndemnisationJustice\Entity\AdministrationType;
use MonIndemnisationJustice\Entity\FDO\EtablissementFDO;
use MonIndemnisationJustice\Entity\GeoCodePostal;
use MonIndemnisationJustice\Entity\GeoCommune;
use MonIndemnisationJustice\Entity\GeoDepartement;
use Psr\Log\LoggerInterface;

/**
 * Importe les données de zone de compétence territoriale gendarmerie et police nationales https://www.data.gouv.fr/fr/datasets/competence-territoriale-gendarmerie-et-police-nationales/.
 */
class ImporteurCompetencesTerritorialesFDO extends AbstractImporteurDataGouv
{
    private const RESSOURCE_COMPETENCE_TERRITORIALE = 'c53cd4d4-4623-4772-9b8c-bc72a9cdf4c2';

    public function __construct(
        protected readonly LoggerInterface $logger,
        protected readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function getResource(): string
    {
        return self::RESSOURCE_COMPETENCE_TERRITORIALE;
    }

    /**
     * @param array{
     *     code_commune: string,
     *     libelle_commune: string,
     *     institution: string,
     *     id_service: string,
     *     service: string,
     *     code_postal: string,
     *     code_postaux?: string,
     *
     * } $entree
     */
    protected function traiterEntree(array $entree): bool
    {
        $commune = $this->em->getRepository(GeoCommune::class)->getOrCreate(
            $entree['code_commune'],
            $entree['libelle_commune'],
            $this->em->getRepository(GeoDepartement::class)->find(
                $entree['code_postal']
            )
        );
        $typeAdministration = AdministrationType::tryFrom($entree['institution']);
        $administration = $typeAdministration ? $this->em->find(Administration::class, 'PN' === $typeAdministration && $commune->getDepartement()?->estPrefectureDePolice() ? AdministrationType::PREFECTURE_DE_POLICE : $typeAdministration) : null;
        $etablissement =
            $this->em->getRepository(EtablissementFDO::class)->getByNom($entree['service']) ??
            new EtablissementFDO()
                ->setIdentifiant($entree['id_service'])
                ->setNom($entree['service'])
                ->setCodePostal(
                    $this->em->getRepository(GeoCodePostal::class)->getOrCreate(
                        $entree['code_postal'],
                        $commune
                    )
                )
                ->setAdministration($administration);

        foreach (explode('-', $entree['codes_postaux']) as $codePostal) {
            $geoCodePostal = $this->em->getRepository(GeoCodePostal::class)->getOrCreate(
                $codePostal,
                $commune
            );
            $etablissement->ajouterCompetence($geoCodePostal);
        }

        $this->em->persist($etablissement);
        $this->em->flush();

        return true;
    }
}
