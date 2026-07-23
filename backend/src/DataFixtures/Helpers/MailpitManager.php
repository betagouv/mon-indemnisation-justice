<?php

namespace MonIndemnisationJustice\DataFixtures\Helpers;

use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class MailpitManager
{
    protected ?Client $client = null;

    public function __construct(
        #[Autowire(env: 'default::MAILPIT_BASE_URL')]
        ?string $mailpitBaseUrl,
    ) {
        $this->client = $mailpitBaseUrl ? new Client([
            'base_uri' => $mailpitBaseUrl,
        ]) : null;
    }

    public function supprimerMessages(): void
    {
        if ($this->client) {
            $this->client->request('DELETE', '/api/v1/messages');
        }

    }
}
