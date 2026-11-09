<?php

namespace MonIndemnisationJustice\Tests\Controller;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Controller\PublicController;
use MonIndemnisationJustice\Entity\Usager;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[CoversClass(PublicController::class)]
class PublicControllerTest extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
    }

    public function testAnonymeAccedeALaPageDeCreationDeCompte(): void
    {
        $this->client->request('GET', '/dysfonctionnement/');

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $argumentsReact = $this->client->getCrawler()->filter('#react-arguments')->text();
        $donnees = json_decode($argumentsReact, true);
        $this->assertArrayHasKey('jetonsCsrf', $donnees);
        $this->assertArrayHasKey('authenticate', $donnees['jetonsCsrf']);
        $this->assertNotEmpty($donnees['jetonsCsrf']['authenticate']);
    }

    public function testUsagerDejaConnecteEstRedirigeVersSonEspace(): void
    {
        $usager = $this->em->getRepository(Usager::class)->findOneBy(['email' => 'raquel.randt@courriel.fr']);
        $this->client->loginUser($usager, 'requerant');

        $this->client->request('GET', '/dysfonctionnement/');

        $this->assertResponseRedirects('/requerant');
    }

    public function testAnonymeEstRedirigeHorsDuTestDEligibilite(): void
    {
        $this->client->request('GET', '/dysfonctionnement/tester-mon-eligibilite/');

        $this->assertResponseRedirects('/dysfonctionnement');
    }

    public function testUsagerConnecteAccedeAuTestDEligibilite(): void
    {
        $usager = $this->em->getRepository(Usager::class)->findOneBy(['email' => 'raquel.randt@courriel.fr']);
        $this->client->loginUser($usager, 'requerant');

        $this->client->request('GET', '/dysfonctionnement/tester-mon-eligibilite/');

        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }
}
