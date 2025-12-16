<?php

namespace MonIndemnisationJustice\Tests\Api\Agent\Fip6\Endpoint\Dossier;

use MonIndemnisationJustice\Tests\Api\Agent\Fip6\Endpoint\APIEndpointTestCase;

/**
 * Teste le point d'entrée @ListerDossierAAttribuerEndpointTest de l'API, listant les dossiers à attribuer.
 *
 * @internal
 *
 * @coversNothing
 */
class ListerDossierAAttribuerEndpointTest extends APIEndpointTestCase
{
    /**
     * ETQ agent attributeur, je dois pouvoir charger la liste des dossiers à attribuer.
     */
    public function testListeOk(): void
    {
        $this->connexion('attributeur@justice.gouv.fr');
        $this->apiGet();

        $this->assertTrue($this->client->getResponse()->isOk());

        /** @var array $output */
        $output = json_decode($this->client->getResponse()->getContent());

        $this->assertCount(1, $output);
    }

    protected function getApiRoute(): string
    {
        return '/api/agent/fip6/dossiers/liste/a-attribuer';
    }
}
