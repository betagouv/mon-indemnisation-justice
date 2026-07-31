<?php

namespace MonIndemnisationJustice\Tests\Api\Agent\FDO\Endpoint\Agent;

use MonIndemnisationJustice\Api\Agent\FDO\Endpoint\Agent\AffecterEtablissementEndpoint;
use MonIndemnisationJustice\Entity\FDO\EtablissementFDO;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\AbstractEndpointTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AffecterEtablissementEndpoint::class)]
class AffecterEtablissementEndpointTest extends AbstractEndpointTestCase
{
    public function testAffectationEtablissementOk(): void
    {
        $commissaire = $this->connexion('commissaire@interieur.gouv.fr');
        $etablissement = $this->em->getRepository(EtablissementFDO::class)->findOneBy([
            'nom' => 'Commissariat de police de Paris 20ème arrondissement',
        ]);

        $this->client->request('POST', '/api/agent/fdo/affecter', [

            'etablissement' => $etablissement->getId()->toString(),
            'dateAffectation' => new \DateTimeImmutable('-164 days')->format('Y-m-d'),
            'gerard' => false,
        ]);

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $this->em->refresh($commissaire);
        $this->assertCount(1, $commissaire->getAffectations());
    }

    public function testAffectationExemptionOk(): void
    {
        $commissaire = $this->connexion('commissaire@interieur.gouv.fr');

        $this->client->request('POST', '/api/agent/fdo/affecter', [
            'etablissement' => null,
            'dateAffectation' => null,
            'estExempt' => true,
        ]);

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $this->em->refresh($commissaire);
        $this->assertEmpty($commissaire->getAffectations());
        $this->assertTrue($commissaire->estExempteAffectation());
    }
}
