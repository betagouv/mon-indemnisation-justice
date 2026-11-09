<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Tests\Api\Public\Dysfonctionnement;

use MonIndemnisationJustice\Api\Public\Dysfonctionnement\SauvegarderTestEligibiliteDysfonctionnementEndpoint;
use MonIndemnisationJustice\Entity\PreuvesDiligenceType;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Dans une classe de test dédiée (et non dans SauvegarderTestEligibiliteDysfonctionnementEndpointTest,
 * dont les autres tests se connectent par défaut) pour avoir un client réellement anonyme dès le départ.
 */
#[CoversClass(SauvegarderTestEligibiliteDysfonctionnementEndpoint::class)]
class SauvegarderTestEligibiliteDysfonctionnementEndpointAnonymeTest extends WebTestCase
{
    protected KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
    }

    public function testRejetteUnAppelAnonyme(): void
    {
        $this->client->request(
            'PUT',
            '/api/public/dysfonctionnement/test-eligibilite',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'procedureTerminee' => true,
                'dateDecision' => '2023-06-15',
                'aUneActionContentieuse' => false,
                'typesDecision' => ['jugement_premiere_instance'],
                'piecesProcedure' => ['acte_introductif', 'ecritures'],
                'preuvesDiligences' => PreuvesDiligenceType::AVEC_JUSTIFICATIFS,
            ]),
        );

        $this->assertFalse($this->client->getResponse()->isSuccessful());
    }
}
