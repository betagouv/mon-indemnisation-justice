<?php

namespace MonIndemnisationJustice\Service\Signature\Docuseal;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Client de l'API d'une instance DocuSeal auto-hébergée (https://www.docuseal.com/docs/api).
 *
 * L'édition open source ne permet de créer des soumissions qu'à partir d'un modèle (template) existant : la création
 * depuis un PDF (`POST /submissions/pdf`) est réservée à l'édition Pro.
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
     * Crée une soumission à partir du modèle `$templateId` pour un unique signataire, et retourne l'identifiant de
     * la soumission.
     *
     * Voir https://www.docuseal.com/docs/api#create-a-submission
     *
     * @throws TransportExceptionInterface
     * @throws \Exception
     */
    public function creerSoumission(int $templateId, string $nom, string $nomSignataire, string $courrielSignataire, ?string $identifiantExterne = null): int
    {
        $response = $this->httpClient->request(
            'POST',
            'submissions',
            [
                'json' => [
                    'template_id' => $templateId,
                    'name' => $nom,
                    'send_email' => true,
                    'submitters' => [
                        array_filter([
                            'name' => $nomSignataire,
                            'email' => $courrielSignataire,
                            'external_id' => $identifiantExterne,
                        ]),
                    ],
                ],
            ]
        );

        // La réponse est la liste des signataires créés, chacun portant l'identifiant de sa soumission
        $data = $this->extraireDonneesReponse($response);

        if (!isset($data[0]['submission_id'])) {
            throw new \Exception("Réponse inattendue de l'API DocuSeal lors de la création de la soumission.");
        }

        return (int) $data[0]['submission_id'];
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
