<?php

namespace MonIndemnisationJustice\Tests\Api\Agent\FDO\Endpoint\Agent;

use MonIndemnisationJustice\Api\Agent\FDO\Endpoint\Agent\MoiEndpoint;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\AbstractEndpointTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(MoiEndpoint::class)]
class MoiEndpointTest extends AbstractEndpointTestCase
{
    public function testAgentFDOAffecteOk(): void
    {
        $gendarme = $this->connexion('gendarme@gendarmerie.interieur.gouv.fr');

        $this->client->request('GET', '/api/agent/fdo/moi');


        $this->assertTrue($this->client->getResponse()->isSuccessful());

        /** @var object $donnees */
        $donnees = json_decode($this->client->getResponse()->getContent());

        $this->assertEquals($gendarme->getId(), $donnees->agent->id);
        $this->assertCount(1, $donnees->agent->affectations);
        $this->assertEquals("Gendarmerie - Brigade d'Auray", $donnees->agent->affectations[0]->etablissement->nom);
    }

    public function testAgentFDOExempteOk(): void
    {
        $agent = $this->connexion('interieur@interieur.gouv.fr');

        $this->client->request('GET', '/api/agent/fdo/moi');

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        /** @var object $donnees */
        $donnees = json_decode($this->client->getResponse()->getContent());

        $this->assertEquals($agent->getId(), $donnees->agent->id);
        $this->assertEquals(false, $donnees->agent->affectations);
    }

    public function testAgentFDONonAffecteOk(): void
    {
        $commissaire = $this->connexion('commissaire@interieur.gouv.fr');

        $this->client->request('GET', '/api/agent/fdo/moi');

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        /** @var object $donnees */
        $donnees = json_decode($this->client->getResponse()->getContent());

        $this->assertEquals($commissaire->getId(), $donnees->agent->id);
        $this->assertEmpty($donnees->agent->affectations);
    }

    public function testAgentFIP6Ko(): void
    {
        $this->connexion('redacteur@justice.gouv.fr');

        $this->client->request('GET', '/api/agent/fdo/moi');

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function testNonConnectKo(): void
    {
        $this->client->request('GET', '/api/agent/fdo/moi');

        $this->assertEquals(Response::HTTP_TEMPORARY_REDIRECT, $this->client->getResponse()->getStatusCode());
    }
}
