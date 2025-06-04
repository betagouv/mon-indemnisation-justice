<?php

namespace MonIndemnisationJustice\Service\DataGouv;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\GeoCommune;
use MonIndemnisationJustice\Entity\GeoDepartement;

class ImporteurGeoCommune implements DataGouvProcessor
{
    // Communes et villes de France https://www.data.gouv.fr/fr/datasets/communes-et-villes-de-france-en-csv-excel-json-parquet-et-feather/

    // Cette source n'est pas fiable, car on n'y trouve pas la liste exhaustive des codes postaux (ex: Marseille =>
    // 13000 mais pas 13005, etc)
    private const RESOURCE_GEO_COMMUNE = '6989ed1a-8ffb-4ef9-b008-340327c99430';

    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function getResource(): string
    {
        return self::RESOURCE_GEO_COMMUNE;
    }

    public function processRecord(array $record): void
    {
        if (null !== $record['code_insee']) {
            /** @var GeoCommune $commune */
            $commune = $this->entityManager->getRepository(GeoCommune::class)->find($record['code_insee']);
            if (null === $commune) {
                $commune = (new GeoCommune())
                ->setCode($record['code_insee'])
                ->setNom($record['nom_standard'])
                ->setDepartement($this->entityManager->getRepository(GeoDepartement::class)->find($record['dep_code']));

                $this->entityManager->persist($commune);
                $this->entityManager->flush();
            }
        }
    }

    public function onProcessed(): void
    {
        // TODO: Implement onProcessed() method.
    }
}
