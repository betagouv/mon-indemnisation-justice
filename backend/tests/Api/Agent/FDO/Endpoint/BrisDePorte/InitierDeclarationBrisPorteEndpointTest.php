<?php

namespace MonIndemnisationJustice\Tests\Api\Agent\FDO\Endpoint\BrisDePorte;

use MonIndemnisationJustice\Api\Agent\FDO\Endpoint\BrisDePorte\InitierDeclarationBrisPorteEndpoint;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\AbstractEndpointTestCase;
use Symfony\Component\Uid\Uuid;

/**
 * Teste le point d'entrée @InitierDeclarationBrisPorteEndpoint de l'API, permettant à l'agent des FDO de créer un
 * brouillon de déclaration.
 *
 * @internal
 *
 * @covers \MonIndemnisationJustice\Api\Agent\FDO\Endpoint\BrisDePorte\InitierDeclarationBrisPorteEndpoint
 */
class InitierDeclarationBrisPorteEndpointTest extends AbstractEndpointTestCase
{
    /**
     * ETQ agent des FDO, je dois pouvoir initier un brouillon de déclaration de bris de porte.
     */
    public function testInitierOk(): void
    {
        $this->connexion('gendarme@gendarmerie.interieur.gouv.fr');

        $this->client->request('PUT', '/api/agent/fdo/bris-de-porte/initier');

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $input = json_decode($this->client->getResponse()->getContent(), false);

        $this->assertIsObject($input);
        $this->assertObjectHasProperty('id', $input);

        $this->assertTrue(Uuid::isValid($input->id));
    }

    /**
     * ETQ agent du MJ, je ne dois pas pouvoir initier un brouillon de déclaration de bris de porte.
     */
    public function testInitierKoPasAgentFDO(): void
    {
        $this->connexion('redacteur@justice.gouv.fr');

        $this->client->request('PUT', '/api/agent/fdo/bris-de-porte/initier');

        $this->assertTrue($this->client->getResponse()->isClientError());

        ['erreur' => $erreur] = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals("La déclaration d'une erreur opérationnelle est retreinte aux agents des Forces de l'Ordre", $erreur);
    }
}
