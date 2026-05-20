<?php

declare(strict_types=1);

namespace Api\Agent\Fip6\Dossier\Endpoint;

use MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier\RechercherDossiersEndpoint;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\AbstractEndpointTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RechercherDossiersEndpoint::class)]
class RechercherDossierEndpointTest extends AbstractEndpointTestCase
{
    public function testRechercheOk(): void
    {
        $this->connexion('attributeur@justice.gouv.fr');

        $this->client->request('GET', '/api/agent/fip6/dossiers/rechercher', ['e' => 'a-finaliser']);

        $this->assertTrue($this->client->getResponse()->isOk());

        ['page' => $page, 'taille' => $taille, 'total' => $total, 'resultats' => $dossiers] = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals(1, $page);
        $this->assertEquals(2, $total);
        $this->assertCount(2, $dossiers);

        foreach ($dossiers as $donneesDossier) {
            $dossier = $this->em->getRepository(Dossier::class)->find($donneesDossier['id']);

            $this->assertEmpty(array_diff_multidimensional([
                'id' => $dossier->getId(),
                'reference' => $dossier->getReference(),
                'etat' => [
                    'id' => $dossier->getEtatDossier()->getId(),
                    'etat' => $dossier->getEtatDossier()->getEtat()->value,
                    'dateEntree' => $dossier->getEtatDossier()->getDate()->format('Y-m-d H:i:s'),
                    'redacteur' => null,
                    'requerant' => true,
                    'contexte' => null,
                ],
                'dateDepot' => $dossier->getDateDeclaration()?->format('Y-m-d H:i:s'),
                'redacteur' => null,
                'requerant' => $dossier->getUsager()->getNomCourant(capital: true),
                'adresse' => $dossier->getBrisPorte()->getAdresse()->getLibelle(),
                'estEligible' => true,
                'typeAttestation' => null,
                'issuDeclarationFDO' => false,
            ], $donneesDossier));
        }
    }
}
