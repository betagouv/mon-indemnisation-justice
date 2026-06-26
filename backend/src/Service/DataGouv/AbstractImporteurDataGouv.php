<?php

namespace MonIndemnisationJustice\Service\DataGouv;

use GuzzleHttp\Client as HttpClient;

abstract class AbstractImporteurDataGouv
{
    protected HttpClient $client;

    public function __construct()
    {
        $this->client = new HttpClient([
            'base_uri' => 'https://tabular-api.data.gouv.fr',
        ]);
    }

    abstract protected function getResource(): string;

    protected function avantImport(): void
    {
    }

    protected function apresImport(): void
    {
    }

    protected function gererErreur(\Exception $e): void
    {
        throw $e;
    }

    public function importer(bool $conserverFichier = false, ?int $limite = null): int
    {
        $total = 0;
        try {
            $this->avantImport();
            foreach ($this->lireRessourceCSV($this->getResource(), $conserverFichier, $limite) as $entree) {
                $total += $this->traiterEntree($entree) ? 1 : 0;
            }

        } catch (\Exception $e) {
            $this->gererErreur($e);
        } finally {
            $this->apresImport();

            return $total;
        }
    }

    abstract protected function traiterEntree(array $entree): bool;

    /**
     * @return \Generator<int, array>
     */
    public function lireRessourceCSV(string $ressource, bool $conserverFichier = false, ?int $limite = null): \Generator
    {
        $fichier = sys_get_temp_dir()."/data_gouv_$ressource.csv";

        if (file_exists($fichier)) {
            if (!$conserverFichier) {
                unlink($fichier);
                file_put_contents($fichier, file_get_contents("https://tabular-api.data.gouv.fr/api/resources/$ressource/data/csv/"));
            }
        } else {
            file_put_contents($fichier, file_get_contents("https://tabular-api.data.gouv.fr/api/resources/$ressource/data/csv/"));
        }

        /** @var array|null $enTetes */
        $enTetes = null;
        $source = fopen($fichier, 'r');
        $compteur = 0;
        while (($entree = fgetcsv($source, separator: ',', escape: '')) !== false && (null === $limite || $compteur < $limite)) {
            if (null === $enTetes) {
                $enTetes = $entree;
            } else {
                yield self::extraireLigne($entree, $enTetes);

                ++$compteur;
            }
        }
        fclose($source);

        if (!$conserverFichier) {
            unlink($fichier);
        }
    }

    /**
     * Retourne un tableau dont les clefs sont les en-têtes et les valeurs celles de l'entrée donnée.
     *
     * @param array $entree  les valeurs retournées pour une entrée / ligne
     * @param array $enTetes les en-têtes lues en début de fichier
     */
    private static function extraireLigne(array $entree, array $enTetes): array
    {
        return array_merge(
            ...array_map(
                fn ($enTete, $index) => [$enTete => $entree[$index] ?? null],
                $enTetes,
                array_keys($enTetes)
            )
        );
    }
}
