<?php

namespace MonIndemnisationJustice\Service\Signature\Youtrust;

use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class YoutrustClient
{
    public function __construct(
        #[Target('yousign.client')]
        protected readonly HttpClientInterface $youtrustClient,
        protected readonly DenormalizerInterface $denormalizer,
        protected readonly NormalizerInterface $normalizer,
    ) {
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
        return $this->extractSignatureRequest(
            $this->youtrustClient->request(
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
            )
        );
    }

    public function uploadDocument(SignatureRequest $request, $body, string $filename): SignatureRequest
    {
        $formData = new FormDataPart(
            [
                'nature' => 'signable_document',
                'file' => new DataPart($body, $filename, 'application/pdf'),
            ]
        );

        return $this->extractSignatureRequest(
            $this->youtrustClient->request(
                'POST',
                "signature_requests/{$request->id}/documents",
                [
                    'headers' => $formData->getPreparedHeaders()->toArray(),
                    'body' => $formData->bodyToIterable(),
                ]
            )
        );
    }

    public function addSigner(SignatureRequest $request, ): SignatureRequest

    {
        return $this->extractSignatureRequest(
             $this->youtrustClient->request(
          	'POST',
            "signature_requests/{$request->id}/signers",
          	[
            	'body' => <<<JSON
                       {
                           "info":{
                              "first_name":"John",
                              "last_name":"Doe",
                              "email":"jdoe@example.com",
                              "locale":"fr"
                           },
                           "fields":[
                              {
                                 "type":"signature",
                                 "document_id": $documentId,
                                 "page":1,
                                 "x":77,
                                 "y":581
                              }
                           ],
                           "signature_level":"electronic_signature",
                           "signature_authentication_mode":"no_otp"
                       }
                       JSON,
            	'headers' => [
                'Content-Type' => 'application/json',
            	],
        ]);

        )
    }

    protected function extractSignatureRequest(ResponseInterface $response): SignatureRequest
    {
        $data = json_decode($response->getContent(false) ?? '{}', true);

        if ($response->getStatusCode() >= 400) {
            throw new \Exception($data['detail'] ?? 'An error occurred while communicating to Youtrust API.');
        }

        return $this->denormalizeRequest($data);
    }

    protected function denormalizeRequest(array $data, ?SignatureRequest $source = null): SignatureRequest
    {
        return $this->denormalizer->denormalize(
            $data,
            SignatureRequest::class,
            context: [
                AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => true,
                // AbstractNormalizer::OBJECT_TO_POPULATE => $source,
                AbstractObjectNormalizer::SKIP_UNINITIALIZED_VALUES => true,
                AbstractObjectNormalizer::DEEP_OBJECT_TO_POPULATE => true,
            ]
        );
    }
}
