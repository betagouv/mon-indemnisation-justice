<?php

namespace MonIndemnisationJustice\Tests\Api\Requerant\Dossier;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\ProfilDeposant;
use MonIndemnisationJustice\Entity\TestEligibiliteDysfonctionnement;
use MonIndemnisationJustice\Entity\Usager;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[CoversClass(\MonIndemnisationJustice\Api\Requerant\Dossier\ConsulterDossierDysfonctionnementEndpoint::class)]
#[CoversClass(\MonIndemnisationJustice\Api\Requerant\Dossier\AmenderDossierDysfonctionnementEndpoint::class)]
class AmenderDossierDysfonctionnementEndpointTest extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
    }

    protected function creerDossierBrouillon(string $email = 'raquel.randt@courriel.fr'): Dossier
    {
        $usager = $this->em->getRepository(Usager::class)->findOneBy(['email' => $email]);

        $test = TestEligibiliteDysfonctionnement::fromArray([
            'dateDecision' => new \DateTimeImmutable('-6 months'),
            'aUneActionContentieuse' => false,
            'typesDecision' => ['jugement_premiere_instance'],
            'piecesProcedure' => ['acte_introductif'],
            'preuvesDiligences' => true,
            'usager' => $usager,
        ]);
        $this->em->persist($test);

        $dossier = Dossier::dysfonctionnementDepuisTestEligibilite($test);
        $this->em->persist($dossier);
        $this->em->flush();

        $this->client->loginUser($usager, 'requerant');

        return $dossier;
    }

    public function testConsulterUnDossierFraichementCree(): void
    {
        $dossier = $this->creerDossierBrouillon();

        $this->client->request('GET', "/api/requerant/dossier/dysfonctionnement/{$dossier->getId()}");

        $this->assertResponseIsSuccessful();
        $donnees = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertNull($donnees['profil']);
        $this->assertNull($donnees['identiteNom']);
    }

    public function testAmenderLeProfil(): void
    {
        $dossier = $this->creerDossierBrouillon();

        $this->client->request(
            'PATCH',
            "/api/requerant/dossier/dysfonctionnement/{$dossier->getId()}/amender",
            content: json_encode(['profil' => ProfilDeposant::AVOCAT->value])
        );

        $this->assertResponseIsSuccessful();
        $donnees = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('avocat', $donnees['profil']);
    }

    public function testAmenderLIdentite(): void
    {
        $dossier = $this->creerDossierBrouillon();

        $this->client->request(
            'PATCH',
            "/api/requerant/dossier/dysfonctionnement/{$dossier->getId()}/amender",
            content: json_encode([
                'identiteNom' => 'Dupont',
                'identitePrenom' => 'Jean',
            ])
        );

        $this->assertResponseIsSuccessful();
        $donnees = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Dupont', $donnees['identiteNom']);
        $this->assertEquals('Jean', $donnees['identitePrenom']);

        // Les valeurs persistent bien en base, pas seulement en mémoire pour cette réponse
        $this->client->request('GET', "/api/requerant/dossier/dysfonctionnement/{$dossier->getId()}");
        $this->assertResponseIsSuccessful();
        $donnees = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Dupont', $donnees['identiteNom']);
    }

    public function testUnAutreUsagerNePeutPasConsulter(): void
    {
        $dossier = $this->creerDossierBrouillon();

        $unAutreUsager = $this->em->getRepository(Usager::class)->findOneBy(['email' => 'ray.keran@courriel.fr']);
        $this->client->loginUser($unAutreUsager, 'requerant');
        $this->client->request('GET', "/api/requerant/dossier/dysfonctionnement/{$dossier->getId()}");

        $this->assertResponseStatusCodeSame(403);
    }
}
