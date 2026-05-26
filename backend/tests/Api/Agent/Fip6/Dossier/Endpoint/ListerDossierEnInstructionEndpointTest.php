<?php

namespace MonIndemnisationJustice\Test\Api\Agent\Fip6\Dossier\Endpoint;

use MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier\ListerDossierEnInstructionEndpoint;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\APIEndpointTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Teste le point d'entrée @ListerDossierEnInstructionEndpoint de l'API, listant les dossiers en cours d'instruction.
 */
#[CoversClass(ListerDossierEnInstructionEndpoint::class)]
class ListerDossierEnInstructionEndpointTest extends APIEndpointTestCase
{
    /**
     * ETQ rédacteur, je dois pouvoir charger la liste de mes dossiers en cours d'instruction.
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
        return '/api/agent/fip6/dossiers/liste/en-instruction';
    }
}
