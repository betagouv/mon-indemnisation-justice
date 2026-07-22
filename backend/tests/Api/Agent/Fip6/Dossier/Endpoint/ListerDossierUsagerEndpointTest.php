<?php

namespace MonIndemnisationJustice\Tests\Api\Agent\Fip6\Dossier\Endpoint;

use MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier\ListerDossierUsagerEndpoint;
use MonIndemnisationJustice\Entity\Usager;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\AbstractEndpointTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ListerDossierUsagerEndpoint::class)]
class ListerDossierUsagerEndpointTest extends AbstractEndpointTestCase
{
    public function testListerDossiersUsagerOk()
    {
        $redacteur = $this->connexion('redacteur@justice.gouv.fr');
        $usager = $this->em->getRepository(Usager::class)->findOneBy(['email' => 'ray.keran@courriel.fr']);

        $this->client->request('GET', "/api/agent/fip6/dossiers/usager/{$usager->getId()}");

        $this->assertTrue($this->client->getResponse()->isOk());

        /** @var array $output */
        $donnees = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayEquals([
            'id' => $usager->getId(),
            'civilite' => $usager->getPersonne()->getCivilite()->value,
            'nom' => $usager->getPersonne()->getNom(),
            'prenom' => $usager->getPersonne()->getPrenom(),
        ], $donnees['usager']);

        $this->assertIsArray($donnees['dossiers']);
        $this->assertEquals(1, count($donnees['dossiers']));
    }
}
