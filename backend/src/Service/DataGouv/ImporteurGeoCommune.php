<?php

namespace MonIndemnisationJustice\Service\DataGouv;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\GeoCommune;
use MonIndemnisationJustice\Entity\GeoDepartement;

class ImporteurGeoCommune extends AbstractImporteurDataGouv
{
    // Communes et villes de France https://www.data.gouv.fr/fr/datasets/communes-et-villes-de-france-en-csv-excel-json-parquet-et-feather/

    // Cette source n'est pas fiable, car on n'y trouve pas la liste exhaustive des codes postaux (ex: Marseille =>
    // 13000 mais pas 13005, etc)
    private const RESOURCE_GEO_COMMUNE = '6989ed1a-8ffb-4ef9-b008-340327c99430';

    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    public function getResource(): string
    {
        return self::RESOURCE_GEO_COMMUNE;
    }

    public function traiterEntree(array $entree): bool
    {
        if (null !== $entree['code_insee']) {
            /** @var GeoCommune $commune */
            $commune = $this->em->getRepository(GeoCommune::class)->find($entree['code_insee']);
            if (null === $commune) {
                $commune = new GeoCommune()
                    ->setCode($entree['code_insee'])
                    ->setNom($entree['nom_standard'])
                    ->setDepartement($this->em->getRepository(GeoDepartement::class)->find($entree['dep_code']));

                $this->em->persist($commune);
            }

            return true;
        }

        return false;
    }

    protected function apresImport(): void
    {
        $this->em->flush();
    }
}
