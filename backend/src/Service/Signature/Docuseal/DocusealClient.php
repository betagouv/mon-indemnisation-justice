<?php

namespace MonIndemnisationJustice\Service\Signature\Docuseal;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Client de l'API DocuSeal (https://www.docuseal.com/docs/api), dans son édition Pro (instance auto-hébergée sous
 * licence, ou offre cloud via https://api.docuseal.eu) : seule cette édition permet de créer une soumission depuis un
 * PDF (`POST /submissions/pdf`).
 */
class DocusealClient
{
    protected readonly HttpClientInterface $httpClient;

    public function __construct(
        HttpClientInterface $httpClient,
        #[Autowire(env: 'default::DOCUSEAL_BASE_API_URI')]
        ?string $baseUri,
        #[Autowire(env: 'default::DOCUSEAL_API_KEY')]
        ?string $apiKey,
    ) {
        $this->httpClient = $httpClient->withOptions([
            'base_uri' => rtrim($baseUri ?? '', '/').'/',
            'headers' => [
                'Accept' => 'application/json',
                'X-Auth-Token' => $apiKey ?? '',
            ],
        ]);
    }

    /**
     * Crée une soumission à partir du document PDF `$contenuPdf`, pour un unique signataire dont le champ de signature
     * est positionné par `$zoneSignature` (clés `x`, `y`, `w`, `h` et `page`, numérotée à partir de 1), et retourne
     * l'identifiant de la soumission.
     *
     * Voir https://www.docuseal.com/docs/api#create-a-submission-from-pdf
     *
     * @param array{x: float, y: float, w: float, h: float, page: int} $zoneSignature
     *
     * @throws TransportExceptionInterface
     * @throws \Exception
     */
    public function creerSoumissionDepuisPdf(
        string $nom,
        string $nomDocument,
        string $contenuPdf,
        array $zoneSignature,
        string $nomSignataire,
        string $courrielSignataire,
        ?string $identifiantExterne = null,
    ): int {
        $role = 'Requérant';

        $response = $this->httpClient->request(
            'POST',
            'submissions/pdf',
            [
                'json' => [
                    'name' => $nom,
                    'send_email' => true,
                    'documents' => [
                        [
                            'name' => $nomDocument,
                            'file' => base64_encode($contenuPdf),
                            'fields' => [
                                [
                                    'name' => 'Signature',
                                    'type' => 'signature',
                                    'role' => $role,
                                    'required' => true,
                                    'areas' => [$zoneSignature],
                                ],
                            ],
                        ],
                    ],
                    'submitters' => [
                        array_filter([
                            'role' => $role,
                            'name' => $nomSignataire,
                            'email' => $courrielSignataire,
                            'external_id' => $identifiantExterne,
                        ]),
                    ],
                ],
            ]
        );

        $data = $this->extraireDonneesReponse($response);

        // La réponse est la soumission créée ; on accepte aussi la liste de signataires renvoyée par `POST /submissions`
        $idSoumission = $data['id'] ?? $data[0]['submission_id'] ?? null;

        if (null === $idSoumission) {
            throw new \Exception("Réponse inattendue de l'API DocuSeal lors de la création de la soumission.");
        }

        return (int) $idSoumission;
    }

    protected function extraireDonneesReponse(ResponseInterface $response): array
    {
        $data = json_decode($response->getContent(false) ?: '{}', true) ?? [];

        if ($response->getStatusCode() >= 400) {
            throw new \Exception($data['error'] ?? "Une erreur est survenue lors de la communication avec l'API DocuSeal.");
        }

        return $data;
    }
}
