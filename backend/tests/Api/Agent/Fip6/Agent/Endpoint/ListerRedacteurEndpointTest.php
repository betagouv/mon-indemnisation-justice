<?php

namespace Api\Agent\Fip6\Agent\Endpoint;

use MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Agent\ListerRedacteurEndpoint;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\AbstractEndpointTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ListerRedacteurEndpoint::class)]
class ListerRedacteurEndpointTest extends AbstractEndpointTestCase
{
    public function testListerOk(): void
    {
        $this->connexion('attributeur@justice.gouv.fr');

        $this->client->request('GET', '/api/agent/fip6/agents/redacteurs');

        $redacteurs = json_decode($this->client->getResponse()->getContent(), false);

        $this->assertEqualsCanonicalizing(
            [
                'Red ACTEUR', "Reda K'THEUR",
            ],
            array_map(
                fn (object $redacteur) => $redacteur->nom,
                $redacteurs
            )
        );
    }
}
