<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Tests\Api\Public\Dysfonctionnement;

use MonIndemnisationJustice\Api\Public\Dysfonctionnement\SauvegarderTestEligibiliteDysfonctionnementEndpoint;
use MonIndemnisationJustice\Entity\TestEligibiliteDysfonctionnement;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\AbstractEndpointTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(SauvegarderTestEligibiliteDysfonctionnementEndpoint::class)]
class SauvegarderTestEligibiliteDysfonctionnementEndpointTest extends AbstractEndpointTestCase
{
    private const PAYLOAD_ELIGIBLE = [
        'procedureTerminee' => true,
        'dateDecision' => '2023-06-15',
        'aUneActionContentieuse' => false,
        'typesDecision' => ['jugement_premiere_instance'],
        'piecesProcedure' => ['assignation', 'ecritures'],
        'preuvesDiligences' => true,
    ];

    public function testCreerUnNouveauTestSansSession(): void
    {
        $this->put(self::PAYLOAD_ELIGIBLE);

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $donnees = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $donnees);
        $this->assertTrue($donnees['estEligible']);

        $test = $this->em->getRepository(TestEligibiliteDysfonctionnement::class)->find($donnees['id']);
        $this->assertNotNull($test);
        $this->assertEquals(['jugement_premiere_instance'], $test->typesDecision);
    }

    public function testMettreAJourUnTestExistantEnSession(): void
    {
        $this->put(self::PAYLOAD_ELIGIBLE);
        $idInitial = json_decode($this->client->getResponse()->getContent(), true)['id'];

        $this->put([
            ...self::PAYLOAD_ELIGIBLE,
            'preuvesDiligences' => false,
        ]);

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $donnees = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals($idInitial, $donnees['id'], "L'id doit être le même : le test existant est mis à jour");
        $this->assertFalse($donnees['estEligible']);

        $this->em->clear();
        $test = $this->em->getRepository(TestEligibiliteDysfonctionnement::class)->find($idInitial);
        $this->assertFalse($test->preuvesDiligences);
    }

    public function testRetourneNonEligibleSiCritereManquant(): void
    {
        $this->put([
            ...self::PAYLOAD_ELIGIBLE,
            'aUneActionContentieuse' => true,
        ]);

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $donnees = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertFalse($donnees['estEligible']);
    }

    public function testErreurValidationSiPayloadIncomplet(): void
    {
        $this->put([
            'procedureTerminee' => true,
            // dateDecision manquante
            'aUneActionContentieuse' => false,
            'typesDecision' => ['jugement_premiere_instance'],
            'piecesProcedure' => ['assignation'],
            'preuvesDiligences' => true,
        ]);

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $this->client->getResponse()->getStatusCode());
    }

    public function testErreurValidationSiTableauVideDePieces(): void
    {
        $this->put([
            ...self::PAYLOAD_ELIGIBLE,
            'piecesProcedure' => [],
        ]);

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $this->client->getResponse()->getStatusCode());
    }

    private function put(array $payload): void
    {
        $this->client->request(
            'PUT',
            '/api/public/dysfonctionnement/test-eligibilite',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload),
        );
    }

    private function initializeSession(array $values): void
    {
        $session = $this->client->getContainer()->get('session.factory')->createSession();
        foreach ($values as $key => $value) {
            $session->set($key, $value);
        }
        $session->save();

        $domains = array_unique(array_map(
            fn (Cookie $cookie) => $cookie->getName() === $session->getName() ? $cookie->getDomain() : '',
            $this->client->getCookieJar()->all()
        )) ?: [''];

        foreach ($domains as $domain) {
            $this->client->getCookieJar()->set(new Cookie($session->getName(), $session->getId(), null, null, $domain));
        }
    }
}
