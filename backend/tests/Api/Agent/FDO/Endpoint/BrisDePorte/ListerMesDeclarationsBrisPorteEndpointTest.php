<?php

namespace MonIndemnisationJustice\Tests\Api\Agent\FDO\Endpoint\BrisDePorte;

use MonIndemnisationJustice\Api\Agent\FDO\Endpoint\BrisDePorte\ListerMesDeclarationsEndpoint;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\AbstractEndpointTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Response;

/**
 * Teste le point d'entrée @ListerMesDeclarationsEndpoint de l'API, qui liste les déclarations et les brouillons de
 * l'agent des FDO.
 *
 * @internal
 */
#[CoversClass(ListerMesDeclarationsEndpoint::class)]
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

        // Le brouillon est exposé avec le même format que la déclaration soumise
        $brouillons = array_values(array_filter($output, fn (object $declaration) => null === $declaration->dateSoumission));
        $this->assertCount(1, $brouillons);
        [$brouillon] = $brouillons;

        $this->assertNull($brouillon->reference);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $brouillon->dateOperation);
        $this->assertEquals('DOUTE', $brouillon->estErreur);
        $this->assertEquals('PARIS', $brouillon->adresse->localite);
        $this->assertEquals('PRO1653', $brouillon->procedure->numeroProcedure);
        $this->assertEquals('policier@interieur.gouv.fr', $brouillon->agent->courriel);
        $this->assertNotFalse(\DateTimeImmutable::createFromFormat(\DateTimeInterface::W3C, $brouillon->dateCreation));
        $this->assertCount(2, $brouillon->piecesJointes);
        $this->assertEquals('pv_fdo', $brouillon->piecesJointes[0]->type);
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
