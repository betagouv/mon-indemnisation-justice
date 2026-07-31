<?php

namespace MonIndemnisationJustice\Tests\Api\Agent\FDO\Endpoint\Etablissement;

use MonIndemnisationJustice\Api\Agent\FDO\Endpoint\Etablissement\RechercherEtablissementFDOEndpoint;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\AbstractEndpointTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RechercherEtablissementFDOEndpoint::class)]
class RechercherEtablissementFDOEndpointTest extends AbstractEndpointTestCase
{
    public function testRechercheOk(): void
    {
        $gendarme = $this->connexion('gendarme@gendarmerie.interieur.gouv.fr');

        $this->client->request(
            'GET',
            '/api/agent/fdo/etablissements/rechercher',
            ['r' => 'aura']
        );


        $this->assertTrue($this->client->getResponse()->isSuccessful());

        /** @var object $donnees */
        $donnees = json_decode($this->client->getResponse()->getContent());
        $resultats = $donnees->resultats;

        $this->assertCount(1, $resultats);
        $this->assertEquals("Gendarmerie - Brigade d'Auray", $resultats[0]->nom);
    }
}
