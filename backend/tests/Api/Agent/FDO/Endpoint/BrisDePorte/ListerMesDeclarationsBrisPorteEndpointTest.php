<?php

namespace Api\Agent\FDO\Endpoint\BrisDePorte;

use MonIndemnisationJustice\Api\Agent\FDO\Endpoint\BrisDePorte\ListerMesDeclarationsEndpoint;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\AbstractEndpointTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Teste le point d'entrée @ListerMesDeclarationsEndpoint de l'API, qui liste les déclarations et les brouillons de
 * l'agent des FDO.
 *
 * @internal
 *
 * @covers \MonIndemnisationJustice\Api\Agent\FDO\Endpoint\BrisDePorte\ListerMesDeclarationsEndpoint
 */
class ListerMesDeclarationsBrisPorteEndpointTest extends AbstractEndpointTestCase
{
    /**
     * ETQ agent des FDO, je dois pouvoir accès à la liste de mes déclarations et brouillons.
     */
    public function testListerOk(): void
    {
        $this->connexion('policier@interieur.gouv.fr');

        $this->client->request('GET', '/api/agent/fdo/bris-de-porte/mes-declarations');

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $output = json_decode($this->client->getResponse()->getContent(), false);

        $this->assertIsArray($output);
        $this->assertCount(2, $output);
    }

    /**
     * ETQ agent du MJ, je ne dois pas avoir accès à la liste des déclarations d'un agent des FDOs.
     */
    public function testListerKoPasFDO(): void
    {
        $this->connexion('redacteur@justice.gouv.fr');

        $this->client->request('GET', '/api/agent/fdo/bris-de-porte/mes-declarations');

        $this->assertTrue(Response::HTTP_FORBIDDEN === $this->client->getResponse()->getStatusCode());
    }
}
