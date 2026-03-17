<?php

namespace MonIndemnisationJustice\Tests\Controller\Requerant;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Controller\Requerant\HomeController;
use MonIndemnisationJustice\Entity\Usager;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[CoversClass(HomeController::class)]
class HomeControllerTest extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
    }

    public function testIndex()
    {
        $requerant = $this->em->getRepository(Usager::class)->findOneBy(['email' => 'raquel.randt@courriel.fr']);

        $this->client->loginUser($requerant, 'requerant');

        $this->client->request('GET', '/requerant');

        // Plus de redirection backend, c'est le router React qui renverra vers le dossier à finaliser
        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }
}
