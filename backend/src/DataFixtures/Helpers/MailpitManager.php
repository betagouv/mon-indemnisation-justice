<?php

namespace MonIndemnisationJustice\DataFixtures\Helpers;

use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class MailpitManager
{
    protected Client $client;

    public function __construct(
        #[Autowire(env: 'default::MAILPIT_BASE_URL')]
        ?string $mailpitBaseUrl,
    ) {
        $this->client = new Client([
            'base_uri' => $mailpitBaseUrl ?? 'http://mailpit:8025',
        ]);
    }

    public function supprimerMessages(): void
    {
        $this->client->request('DELETE', '/api/v1/messages');
    }
}
