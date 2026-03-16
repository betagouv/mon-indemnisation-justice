<?php

namespace MonIndemnisationJustice\Tests\Api\Agent\Fip6\Dossier\Endpoint;

use MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier\ListerDossierAInstruireEndpoint;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\APIEndpointTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Teste le point d'entrée @ListerDossierAInstruireEndpoint de l'API, listant les dossiers à instruire.
 */
#[CoversClass(ListerDossierAInstruireEndpoint::class)]
class ListerDossierAInstruireEndpointTest extends APIEndpointTestCase
{
    /**
     * ETQ rédacteur, je dois pouvoir charger la liste de mes dossiers à instruire.
     */
    public function testListeOk(): void
    {
        $this->connexion('redacteur@justice.gouv.fr');
        $this->apiGet();

        $this->assertTrue($this->client->getResponse()->isOk());

        /** @var array $output */
        $output = json_decode($this->client->getResponse()->getContent());

        $this->assertCount(2, $output);
    }

    protected function getApiRoute(): string
    {
        return '/api/agent/fip6/dossiers/liste/a-instruire';
    }
}
