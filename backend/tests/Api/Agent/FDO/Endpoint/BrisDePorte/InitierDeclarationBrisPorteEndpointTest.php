<?php

namespace Api\Agent\FDO\Endpoint\BrisDePorte;

use MonIndemnisationJustice\Api\Agent\FDO\Endpoint\BrisDePorte\InitierDeclarationBrisPorteEndpoint;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\Endpoint\AbstractEndpointTestCase;

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
     * ETQ agent betagouv, je dois pouvoir renseigner les méta-données de l'attestation d'un dossier qui existe.
     */
    public function testInitierOk(): void
    {
        $this->connexion('gendarme@gendarmerie.interieur.gouv.fr');

        $this->client->request('PUT', '/api/agent/fdo/bris-de-porte/initier');

        dump($this->client->getResponse()->getContent());
        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }
}
