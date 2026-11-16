<?php

namespace MonIndemnisationJustice\Service\Signature\Prestataire\Requerant\Documenso;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Client de l'API v2 d'une instance Documenso auto-hébergée (https://docs.documenso.com/developers/public-api).
 */
class DocumensoClient
{
    protected readonly HttpClientInterface $httpClient;

    public function __construct(
        HttpClientInterface $httpClient,
        #[Autowire(env: 'default::DOCUMENSO_BASE_API_URI')]
        ?string $baseUri,
        #[Autowire(env: 'default::DOCUMENSO_API_KEY')]
        ?string $apiKey,
    ) {
        $this->httpClient = $httpClient->withOptions([
            'base_uri' => rtrim($baseUri ?? '', '/').'/',
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$apiKey}",
            ],
        ]);
    }

    /**
     * Crée une enveloppe (à l'état de brouillon) à partir du document PDF `$contenuPdf`, pour un unique signataire dont
     * le champ de signature est positionné par `$zoneSignature` (clés `page`, numérotée à partir de 1, puis
     * `positionX`, `positionY`, `width` et `height`, en pourcentage de la page), et retourne l'identifiant de
     * l'enveloppe.
     *
     * Voir `POST /api/v2/envelope/create`
     *
     * @param array{page: int, positionX: float, positionY: float, width: float, height: float} $zoneSignature
     *
     * @throws TransportExceptionInterface
     * @throws \Exception
     */
    public function creerEnveloppe(
        string $titre,
        string $nomFichier,
        string $contenuPdf,
        array $zoneSignature,
        string $nomSignataire,
        string $courrielSignataire,
        ?string $identifiantExterne = null,
    ): string {
        $formData = new FormDataPart([
            'payload' => json_encode(array_filter([
                'type' => 'DOCUMENT',
                'title' => $titre,
                'externalId' => $identifiantExterne,
                'recipients' => [
                    [
                        'email' => $courrielSignataire,
                        'name' => $nomSignataire,
                        'role' => 'SIGNER',
                        'fields' => [
                            ['type' => 'SIGNATURE', 'identifier' => 0] + $zoneSignature,
                        ],
                    ],
                ],
                'meta' => [
                    'language' => 'fr',
                    'timezone' => 'Europe/Paris',
                ],
            ])),
            'files' => new DataPart($contenuPdf, $nomFichier, 'application/pdf'),
        ]);

        $response = $this->httpClient->request(
            'POST',
            'envelope/create',
            [
                'headers' => $formData->getPreparedHeaders()->toArray(),
                'body' => $formData->bodyToIterable(),
            ]
        );

        $data = $this->extraireDonneesReponse($response);

        if (!isset($data['id'])) {
            throw new \Exception("Réponse inattendue de l'API Documenso lors de la création de l'enveloppe.");
        }

        return (string) $data['id'];
    }

    /**
     * Envoie l'enveloppe aux signataires, par courriel.
     *
     * Voir `POST /api/v2/envelope/distribute`
     *
     * @throws TransportExceptionInterface
     * @throws \Exception
     */
    public function distribuerEnveloppe(string $idEnveloppe): void
    {
        $response = $this->httpClient->request(
            'POST',
            'envelope/distribute',
            [
                'json' => [
                    'envelopeId' => $idEnveloppe,
                ],
            ]
        );

        $this->extraireDonneesReponse($response);
    }

    /**
     * Retourne le détail de l'enveloppe : `status`, ainsi que les listes `envelopeItems` (documents, de la forme
     * `{id, envelopeId, documentDataId, title, order}`), `recipients` et `fields`.
     *
     * Voir `GET /api/v2/envelope/{envelopeId}`
     *
     * @throws TransportExceptionInterface
     * @throws \Exception
     */
    public function recupererEnveloppe(string $idEnveloppe): array
    {
        return $this->extraireDonneesReponse(
            $this->httpClient->request('GET', 'envelope/'.rawurlencode($idEnveloppe))
        );
    }

    /**
     * Retourne le contenu PDF d'un document (« envelope item ») de l'enveloppe.
     *
     * La version `signed` (par défaut) inclut toutes les signatures ainsi que le dossier de preuve, `original` est le
     * document déposé et `pending` le document avec les champs déjà remplis (uniquement pour une enveloppe en cours).
     *
     * Voir `GET /api/v2/envelope/item/{envelopeItemId}/download`
     *
     * @param 'signed'|'original'|'pending' $version
     *
     * @throws TransportExceptionInterface
     * @throws \Exception
     */
    public function telechargerDocument(string $idDocument, string $version = 'signed'): string
    {
        return $this->extraireContenuBinaire(
            $this->httpClient->request('GET', 'envelope/item/'.rawurlencode($idDocument).'/download', [
                'query' => ['version' => $version],
                // Le PDF, et le JSON pour le corps des réponses en erreur
                'headers' => ['Accept' => 'application/pdf, application/json;q=0.9, */*;q=0.8'],
            ])
        );
    }

    /**
     * Retourne le contenu PDF signé du premier document de l'enveloppe `$idEnveloppe` (la déclaration d'acceptation
     * étant le seul document déposé à la création de l'enveloppe).
     *
     * @throws TransportExceptionInterface
     * @throws \Exception
     */
    public function telechargerDocumentSigne(string $idEnveloppe): string
    {
        $documents = $this->recupererEnveloppe($idEnveloppe)['envelopeItems'] ?? [];
        usort($documents, fn (array $a, array $b) => ($a['order'] ?? 0) <=> ($b['order'] ?? 0));

        if (!isset($documents[0]['id'])) {
            throw new \Exception("Aucun document trouvé dans l'enveloppe Documenso {$idEnveloppe}.");
        }

        return $this->telechargerDocument((string) $documents[0]['id']);
    }

    protected function extraireDonneesReponse(ResponseInterface $response): array
    {
        $data = json_decode($response->getContent(false) ?: '{}', true) ?? [];

        if ($response->getStatusCode() >= 400) {
            throw new \Exception($data['message'] ?? "Une erreur est survenue lors de la communication avec l'API Documenso.");
        }

        return $data;
    }

    /**
     * @throws \Exception
     */
    protected function extraireContenuBinaire(ResponseInterface $response): string
    {
        if ($response->getStatusCode() >= 400) {
            $data = json_decode($response->getContent(false) ?: '{}', true);

            throw new \Exception((is_array($data) ? $data['message'] ?? null : null) ?? "Une erreur est survenue lors du téléchargement du document depuis l'API Documenso.");
        }

        return $response->getContent();
    }
}
