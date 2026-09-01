<?php

namespace MonIndemnisationJustice\Tests\Api\Agent\Fip6\Dossier\Endpoint;

use MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier\ListerDossierAVerifierEndpoint;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\AbstractEndpointTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Teste le point d'entrée @ListerDossierAVerifierEndpoint de l'API, listant les dossiers à vérifier.
 */
#[CoversClass(ListerDossierAVerifierEndpoint::class)]
class ListerDossierAVerifierEndpointTest extends AbstractEndpointTestCase
{
    /**
     * ETQ rédacteur, je dois pouvoir charger la liste de mes dossiers à vérifier.
     */
    public function testListeOk(): void
    {
        $this->connexion('redacteur@justice.gouv.fr');
        $this->client->request('GET', '/api/agent/fip6/dossiers/liste/a-verifier');

        $this->assertTrue($this->client->getResponse()->isOk());

        /** @var array $output */
        $output = json_decode($this->client->getResponse()->getContent());

        $this->assertCount(1, $output);
    }

    protected function getApiRoute(): string
    {
        return '/api/agent/fip6/dossiers/liste/a-instruire';
    }
}
