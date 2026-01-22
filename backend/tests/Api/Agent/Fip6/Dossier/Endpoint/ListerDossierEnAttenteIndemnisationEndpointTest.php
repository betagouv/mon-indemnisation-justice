<?php

namespace MonIndemnisationJustice\Tests\Api\Agent\Fip6\Dossier\Endpoint;

use MonIndemnisationJustice\Tests\Api\Agent\Fip6\APIEndpointTestCase;

/**
 * Teste le point d'entrée @ListeDossierEnAttenteIndemnisationEndpoint de l'API, listant les dossiers transmis à FIP6 et
 * dont le versement de l'indemnisation est attendu.
 *
 * @internal
 *
 * @coversNothing
 */
class ListerDossierEnAttenteIndemnisationEndpointTest extends APIEndpointTestCase
{
    /**
     * ETQ agent attributeur, je dois pouvoir charger la liste des dossiers à attribuer.
     */
    public function testListeOk(): void
    {
        $this->connexion('liaison@justice.gouv.fr');
        $this->apiGet();

        $this->assertTrue($this->client->getResponse()->isOk());

        /** @var array $output */
        $output = json_decode($this->client->getResponse()->getContent());

        $this->assertCount(1, $output);
    }

    protected function getApiRoute(): string
    {
        return '/api/agent/fip6/dossiers/liste/en-attente-indemnisation';
    }
}
