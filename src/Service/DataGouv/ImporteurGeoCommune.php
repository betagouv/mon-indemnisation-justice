<?php

namespace MonIndemnisationJustice\Service\DataGouv;

class ImporteurGeoCommune implements DataGouvProcessor
{
    // Communes et villes de France https://www.data.gouv.fr/fr/datasets/communes-et-villes-de-france-en-csv-excel-json-parquet-et-feather/
    private const RESOURCE_GEO_COMMUNE = 'f5df602b-3800-44d7-b2df-fa40a0350325';

    public function getResource(): string
    {
        return self::RESOURCE_GEO_COMMUNE;
    }

    public function processRecord(array $record): void
    {
        // TODO: Implement processRecord() method.
    }

    public function onProcessed(): void
    {
        // TODO: Implement onProcessed() method.
    }
}
