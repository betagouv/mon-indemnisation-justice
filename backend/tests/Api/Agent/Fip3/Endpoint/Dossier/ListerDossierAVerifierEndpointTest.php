<?php

namespace MonIndemnisationJustice\Tests\Api\Agent\Fip3\Endpoint\Dossier;

use MonIndemnisationJustice\Tests\Api\Agent\Fip3\Endpoint\AbstractEndpointTestCase;

/**
 * Teste le point d'entrée @ListeDossierAVerifierEndpoint de l'API, listant les dossiers dont la déclaration
 * d'acceptation vient d'être signée et retournée par le requérant et attend d'être vérifiée.
 *
 * @internal
 *
 * @coversNothing
 */
class ListerDossierAVerifierEndpointTest extends AbstractEndpointTestCase
{
    /**
     * ETQ agent attributeur, je dois pouvoir charger la liste des dossiers à attribuer.
     */
    public function testListeOk(): void
    {
        $this->connexion('redacteur@justice.gouv.fr');
        $this->apiGet();

        $this->assertTrue($this->client->getResponse()->isOk());

        /** @var array $output */
        $output = json_decode($this->client->getResponse()->getContent());

        $this->assertCount(1, $output);
    }

    protected function getApiRoute(): string
    {
        return '/api/agent/fip3/dossiers/liste/a-verifier';
    }
}
