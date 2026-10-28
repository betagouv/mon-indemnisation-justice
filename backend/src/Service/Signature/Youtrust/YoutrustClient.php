<?php

namespace MonIndemnisationJustice\Service\Signature\Youtrust;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class YoutrustClient
{
    protected const string BASE_URI_PRODUCTION = 'https://api.yousign.app/v3/';
    protected const string BASE_URI_SANDBOX = 'https://api-sandbox.yousign.app/v3/';

    protected readonly HttpClientInterface $httpClient;
    public readonly bool $estProduction;

    public function __construct(
        HttpClientInterface $httpClient,
        #[Autowire(env: 'default::YOUTRUST_API_KEY')]
        ?string $apiKey,
        #[Autowire('%kernel.environment%')]
        string $environnement,
        #[Autowire(env: 'default::YOUTRUST_BASE_URI')]
        ?string $baseUri = null,
    ) {
        $baseUri = $baseUri ?: ('prod' === $environnement ? self::BASE_URI_PRODUCTION : self::BASE_URI_SANDBOX);

        $this->estProduction = !str_contains($baseUri, 'sandbox');

        $this->httpClient = $httpClient->withOptions([
            'base_uri' => $baseUri,
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$apiKey}",
            ],
        ]);
    }

    /**
     * Voir la doc https://developers.youtrust.com/reference/post-signature_requests-1.
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     */
    public function initiateSignatureRequest(string $name): SignatureRequest
    {
        $response = $this->httpClient->request(
            'POST',
            'signature_requests',
            [
                'body' => json_encode(
                    [
                        'name' => $name,
                        'delivery_mode' => 'none',
                        'timezone' => 'Europe/Paris',
                    ]
                ),
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]
        );

        $data = $this->extractResponseData($response);

        return new SignatureRequest()
            ->setId(Uuid::fromString($data->id))
            ->setName($data->name)
            ->setStatus($data->status);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function uploadDocument(SignatureRequest $request, mixed $body, string $filename): SignatureRequest
    {
        $formData = new FormDataPart(
            [
                'nature' => 'signable_document',
                'file' => new DataPart($body, $filename, 'application/pdf'),
            ]
        );


        $response = $this->httpClient->request(
            'POST',
            "signature_requests/{$request->getId()}/documents",
            [
                'headers' => $formData->getPreparedHeaders()->toArray(),
                'body' => $formData->bodyToIterable(),
            ]
        );

        $data = $this->extractResponseData($response);

        return $request
            ->setDocumentId($data->id);
    }

    public function addSigner(SignatureRequest $request, string $firstName, string $lastName, string $email, string $locale): SignatureRequest
    {
        $response = $this->httpClient->request(
            'POST',
            "signature_requests/{$request->getId()}/signers",
            [
                'body' => json_encode(
                    [
                        'info' => [
                            'first_name' => 'John',
                            'last_name' => 'Doe',
                            'email' => 'jdoe@example.com',
                            'locale' => 'fr',
                        ],
                        'fields' => [
                            [
                                // TODO
                                'type' => 'signature',
                                'document_id' => $request->getDocumentId(),
                                'page' => 1,
                                'x' => 77,
                                'y' => 581,
                                'height' => 197,
                                'width' => 581,
                            ],
                        ],
                        'signature_level' => 'electronic_signature',
                        'signature_authentication_mode' => 'no_otp',
                    ]
                ),
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]
        );

        $this->extractResponseData($response);

        return $request;
    }

    public function activateSignatureRequest(SignatureRequest $request): SignatureRequest
    {
        $response = $this->httpClient->request('POST', "signature_requests/{$request->getId()}/activate", [
            'headers' => [
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ],
        ]);

        $data = $this->extractResponseData($response);

        return $request->setStatus($data->status);
    }

    protected function extractResponseData(ResponseInterface $response): object
    {
        $data = json_decode($response->getContent(false) ?? '{}', false);

        if ($response->getStatusCode() >= 400) {
            throw new \Exception($data['detail'] ?? 'An error occurred while communicating to Youtrust API.');
        }

        return $data;
    }
}
