<?php

namespace Api\Agent\Fip6\Dossier\Endpoint;

use MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier\DecompterDossierEndpoint;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\AbstractEndpointTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DecompterDossierEndpoint::class)]
class DecompterDossierEndpointTest extends AbstractEndpointTestCase
{
    public function testDecompterDossierRedacteurOk()
    {
        $redacteur = $this->connexion('redacteur@justice.gouv.fr');

        $this->client->request('GET', '/api/agent/fip6/decompter-dossiers');

        $this->assertTrue($this->client->getResponse()->isOk());

        /** @var array $output */
        $donnees = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayEquals([
            'a-instruire' => $redacteur->nbDossiersAInstruire(),
            'en-instruction' => $redacteur->nbDossiersEnInstruction(),
            'a-verifier' => $redacteur->nbDossiersAVerifier(),
        ], $donnees);
    }
}
