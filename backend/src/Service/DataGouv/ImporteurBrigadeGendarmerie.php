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

class ImporteurBrigadeGendarmerie extends AbstractImporteurDataGouv
{
    // Liste des unités de gendarmerie accueillant du public, comprenant leur géolocalisation et leurs horaires d'ouverture https://www.data.gouv.fr/fr/datasets/liste-des-unites-de-gendarmerie-accueillant-du-public-comprenant-leur-geolocalisation-et-leurs-horaires-douverture/
    private const RESOURCE_BRIGADE_GENDARMERIE = '061a5736-8fc2-4388-9e55-8cc31be87fa0';

    protected readonly Administration $administration;

    public function __construct(
        protected readonly LoggerInterface $logger,
        protected readonly EntityManagerInterface $em,
    ) {
        parent::__construct();

        $this->administration = $this->em->getRepository(Administration::class)->find(['type' => AdministrationType::GENDARMERIE_NATIONALE]);
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

        $codePostal = $this->em->getRepository(GeoCodePostal::class)->findOneBy(
            [
                'codePostal' => $entree['code_postal'],
            ]
        ) ?? new GeoCodePostal()
            ->setCodePostal($entree['code_postal'])
            ->setCommune(
                $this->em->getRepository(GeoCommune::class)->find($entree['code_commune_insee']) ?? new GeoCommune()->setCode($entree['code_commune_insee'])->setNom($entree['commune'])
                ->setDepartement(
                    $entree['code_postal'] ?

                        $this->em->getRepository(GeoDepartement::class)
                            ->find($codeDepartement = GeoDepartement::extraireCodeDepuisCodePostal($entree['code_postal'])) ?? new GeoDepartement()->setCode($codeDepartement)->setNom($entree['departement'])
                        : null
                )
            );


        if ($codePostal) {
            $etablissement = $this->em->getRepository(EtablissementFDO::class)->findOneBy(
                [
                    'administration' => $this->administration,
                    'codePostal' => $codePostal,
                ]
            ) ?? new EtablissementFDO()->setAdministration($this->administration)
                ->setCodePostal($codePostal);

            // TODO ajouter l'adresse & le numéro de téléphone
            $adresse = self::extraireAdresse($entree['adresse_geographique']);

            dump($adresse);

            $etablissement
                ->setAdresse($adresse)
                ->setTelephone(substr(preg_replace('/\s/', '', $entree['telephone']), 0, 16))
                ->setNom($entree['service'])
                ->setIdentifiant($entree['identifiant_public_unite']);

            return true;
        }

        return false;
    }

    protected function apresImport(): void
    {
        $this->em->flush();
    }
}
