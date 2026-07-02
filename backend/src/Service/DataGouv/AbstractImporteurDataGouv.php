<?php

namespace MonIndemnisationJustice\Service\DataGouv;

use GuzzleHttp\Client as HttpClient;
use MonIndemnisationJustice\Entity\Adresse;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractImporteurDataGouv
{
    protected HttpClient $client;
    protected Filesystem $filesystem;

    // private const REGEX_FORMAT_ADRESSE = '/^(?P<numero>(\d+|null)) ?(?P<ligne1>.+)(?P<ligne2>-\s.+)\$(?P<code_postal>\d{5,6})\s+(?<commune>.+)$/';
    private const REGEX_FORMAT_ADRESSE = '/^((?P<numero>\d+)\s+)?(?P<ligne1>.*)(\s+-\s+(?P<ligne2>.+))?(?P<code_postal>\d{5})\s+(?P<commune>.+)$/';

    public function __construct()
    {
        $this->client = new HttpClient([
            'base_uri' => 'https://tabular-api.data.gouv.fr',
        ]);
        $this->filesystem = new Filesystem();
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
        $fichier = sprintf('%s/data_gouv_%s_%s.csv', sys_get_temp_dir(), $ressource, date('Y-m-d'));
        $uri = "https://tabular-api.data.gouv.fr/api/resources/$ressource/data/csv/";

        if ($this->filesystem->exists($fichier)) {
            if (!$conserverFichier) {
                $this->filesystem->remove($fichier);
                $this->client->request('GET', $uri, ['sink' => $fichier]);
            }
        } else {
            $this->client->request('GET', $uri, ['sink' => $fichier]);
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
            $this->filesystem->remove($fichier);
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

    /**
     * Les données d'adresse Data.gouv sont de ce format :
     *  "null Rue Charles Domercq - Hall 1$33000 Bordeaux"
     *
     * Soit :
     *  [<numéro>] <ligne1> [- <ligne2>]$<code_postal> <commune>
     */
    public static function extraireAdresse(string $valeur): ?Adresse
    {
        if (!preg_match(self::REGEX_FORMAT_ADRESSE, $valeur, $correspondances)) {
            return null;
        }

        [
            'numero' => $numero,
            'ligne1' => $ligne1,
            'ligne2' => $ligne2,
            'code_postal' => $codePostal,
            'commune' => $commune,
        ] = $correspondances;

        return new Adresse()
            ->setLigne1(!empty($numero) && 'null' !== $numero ? "$numero " : ''.$ligne1)
            ->setLigne2(!empty($ligne2) ? $ligne2 : null)
            ->setCodePostal($codePostal)
            ->setLocalite($commune);
    }
}
