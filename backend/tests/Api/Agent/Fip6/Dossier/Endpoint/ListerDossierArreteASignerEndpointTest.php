<?php

namespace MonIndemnisationJustice\Tests\Api\Agent\Fip6\Dossier\Endpoint;

use MonIndemnisationJustice\Tests\Api\Agent\Fip6\APIEndpointTestCase;

/**
 * Teste le point d'entrée @ListerDossierArreteASignerEndpoint de l'API, listant les dossiers dont l'arrêté de paiement
 * est à signer.
 *
 * @internal
 *
 * @coversNothing
 */
class ListerDossierArreteASignerEndpointTest extends APIEndpointTestCase
{
    /**
     * ETQ agent attributeur, je dois pouvoir charger la liste des dossiers à attribuer.
     */
    public function testListeOk(): void
    {
        $this->connexion('validateur@justice.gouv.fr');
        $this->apiGet();

        $this->assertTrue($this->client->getResponse()->isOk());

        /** @var array $output */
        $output = json_decode($this->client->getResponse()->getContent());

        $this->assertCount(1, $output);
    }

    protected function getApiRoute(): string
    {
        return '/api/agent/fip6/dossiers/liste/arrete-a-signer';
    }
}
