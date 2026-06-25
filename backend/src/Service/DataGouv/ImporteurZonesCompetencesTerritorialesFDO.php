<?php

namespace MonIndemnisationJustice\Service\DataGouv;

use GuzzleHttp\Client as HttpClient;
use Psr\Log\LoggerInterface;

class ImporteurZonesCompetencesTerritorialesFDO
{
    //  Compétence territoriale gendarmerie et police nationales https://www.data.gouv.fr/fr/datasets/competence-territoriale-gendarmerie-et-police-nationales/
    private const REFERENCE_COMPETENCE_TERRITORIALE = 'c53cd4d4-4623-4772-9b8c-bc72a9cdf4c2';

    protected HttpClient $client;

    public function __construct(
        protected readonly LoggerInterface $logger,
    ) {
        $this->client = new HttpClient([
            'base_uri' => 'https://tabular-api.data.gouv.fr',
        ]);
    }

    public function importer(): void
    {
        $idRessource = 'c53cd4d4-4623-4772-9b8c-bc72a9cdf4c2';

        $fichier = sys_get_temp_dir()."/data_gouv_$idRessource.csv";

        file_put_contents($fichier, file_get_contents("https://tabular-api.data.gouv.fr/api/resources/$idRessource/data/csv/"));

        $compteur = 0;
        /** @var array|null $enTetes */
        $enTetes = null;
        $handle = fopen($fichier, 'r');
        while (($row = fgetcsv($handle)) !== false && $compteur < 10) {
            print_r($row);
            if (null === $enTetes) {
                $enTetes = $row;
            } else {
                dump(
                    array_reduce(
                        $row,
                        fn ($carry, $value) => array_merge($carry, [$enTetes[array_search($value, $row)] => $value]),
                        []
                    )
                );
                ++$compteur;
            }
        }
        fclose($handle);

        unlink($fichier);
    }
}
