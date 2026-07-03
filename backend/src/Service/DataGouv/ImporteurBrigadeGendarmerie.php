<?php

namespace MonIndemnisationJustice\Service\DataGouv;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\Administration;
use MonIndemnisationJustice\Entity\AdministrationType;
use MonIndemnisationJustice\Entity\FDO\EtablissementFDO;
use MonIndemnisationJustice\Entity\GeoCodePostal;
use MonIndemnisationJustice\Entity\GeoCommune;
use MonIndemnisationJustice\Entity\GeoDepartement;
use MonIndemnisationJustice\Repository\EtablissementFDORepository;
use MonIndemnisationJustice\Repository\GeoCodePostalRepository;
use MonIndemnisationJustice\Repository\GeoCommuneRepository;
use MonIndemnisationJustice\Repository\GeoDepartementRepository;
use Psr\Log\LoggerInterface;

class ImporteurBrigadeGendarmerie extends AbstractImporteurDataGouv
{
    // Liste des unités de gendarmerie accueillant du public, comprenant leur géolocalisation et leurs horaires d'ouverture https://www.data.gouv.fr/fr/datasets/liste-des-unites-de-gendarmerie-accueillant-du-public-comprenant-leur-geolocalisation-et-leurs-horaires-douverture/
    private const RESOURCE_BRIGADE_GENDARMERIE = '061a5736-8fc2-4388-9e55-8cc31be87fa0';

    protected readonly Administration $administration;
    protected readonly GeoCodePostalRepository $geoCodePostalRepository;
    protected readonly GeoCommuneRepository $geoCommuneRepository;
    protected readonly GeoDepartementRepository $geoDepartementRepository;
    protected readonly EtablissementFDORepository $etablissementFDORepository;

    public function __construct(
        protected readonly LoggerInterface $logger,
        protected readonly EntityManagerInterface $em,
    ) {
        parent::__construct();

        $this->administration = $this->em->getRepository(Administration::class)->find(['type' => AdministrationType::GENDARMERIE_NATIONALE]);

        $this->geoCodePostalRepository = $this->em->getRepository(GeoCodePostal::class);
        $this->geoCommuneRepository = $this->em->getRepository(GeoCommune::class);
        $this->geoDepartementRepository = $this->em->getRepository(GeoDepartement::class);
        $this->etablissementFDORepository = $this->em->getRepository(EtablissementFDO::class);
    }

    protected function getResource(): string
    {
        return self::RESOURCE_BRIGADE_GENDARMERIE;
    }

    /**
     * @param array{
     *     url: string,
     *     voie: string,
     *     commune: string,
     *     service: string,
     *     telephone: string,
     *     code_postal: string,
     *     departement: string,
     *     geocodage_x: string,
     *     geocodage_y: string,
     *     geocodage_epsg: string,
     *     geocodage_x_GPS: float,
     *     geocodage_y_GPS: float,
     *     horaires_accueil: string,
     *     code_commune_insee: string,
     *     adresse_geographique: string,
     *     identifiant_public_unite: int
     * } $entree
     */
    protected function traiterEntree(array $entree): bool
    {
        if (!empty($entree['identifiant_public_unite'])) {


            $codePostal = $this->geoCodePostalRepository->getOrCreate(
                $entree['code_postal'],
                $this->geoCommuneRepository->getOrCreate(
                    $entree['code_commune_insee'],
                    $entree['commune'],
                    $this->geoDepartementRepository->getOrCreate(
                        $entree['code_postal'],
                        $entree['departement']
                    )
                ),
            );

            $this->em->persist($codePostal);

            $etablissement = $this->etablissementFDORepository->getOrCreate(
                $this->administration,
                $codePostal
            );

            $adresse = self::extraireAdresse($entree['adresse_geographique']);

            $etablissement
                ->setAdresse($adresse)
                ->setTelephone(substr(preg_replace('/\s/', '', $entree['telephone']), 0, 16))
                ->setNom($entree['service'])
                ->setIdentifiant($entree['identifiant_public_unite']);

            $this->em->persist($etablissement);
            $this->em->flush();

            return true;
        }


        return false;
    }
}
