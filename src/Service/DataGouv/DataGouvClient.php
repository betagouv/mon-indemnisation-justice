<?php

namespace MonIndemnisationJustice\Service\DataGouv;

use GuzzleHttp\Client as HttpClient;
use Psr\Log\LoggerInterface;

/**
 * Classe qui permet d'accéder aux données.ouverte de https://data.gouv.fr via l'API tabulaire
 * https://www.data.gouv.fr/fr/dataservices/api-tabulaire-data-gouv-fr-beta/.
 */
class DataGouvClient
{
    protected HttpClient $client;

    public function __construct(
        protected readonly LoggerInterface $logger,
        protected readonly ImporteurGeoPays $importeurGeoPays,
    ) {
        $this->client = new HttpClient([
            'base_uri' => 'https://tabular-api.data.gouv.fr',
        ]);
    }

    public function importerGeoPays(): void
    {
        $this->importer($this->importeurGeoPays);
    }

    protected function importer(DataGouvProcessor $processeur, int $pageSize = 50): void
    {
        $dataUri = $this->getResourceDataUri($processeur->getResource(), $pageSize).'?'.http_build_query(['page_size' => $pageSize]);
        $page = 0;

        while (null !== $dataUri) {
            $response = $this->request($dataUri);

            $this->logger->info("Traitement des données de la page $page");

            foreach ($response['data'] as $entree) {
                $processeur->processRecord($entree);
            }

            $page = $response['meta']['page'];
            $dataUri = $response['links']['next'] ?? null;
        }
    }

    protected function getResourceDataUri(string $id, int $pageSize): ?string
    {
        $response = $this->request("/api/resources/$id/");

        foreach ($response['links'] as $link) {
            if ('data' === $link['rel']) {
                return $link['href'];
            }
        }

        return null;
    }

    protected function request(string $uri): array
    {
        $response = $this->client->get($uri);

        return json_decode($response->getBody()->getContents(), true);
    }
}
