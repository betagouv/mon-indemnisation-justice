<?php

namespace MonIndemnisationJustice\Tests\Controller\Agent;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Controller\Agent\DossierController;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\Dossier;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[CoversClass(DossierController::class)]
class DossierControllerTest extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testConsulterDossier(): void
    {
        $agent = $this->em->getRepository(Agent::class)->findOneBy(['email' => 'redacteur@justice.gouv.fr']);
        $dossier = $this->em->getRepository(Dossier::class)->findOneBy(['reference' => 'BRI/20250103/001']);

        $this->client->loginUser($agent, 'agent');

        // TODO: tester la route API à la place
        $this->client->request('GET', "/agent/redacteur/dossier/{$dossier->getId()}");

        $this->assertResponseRedirects("/agent/fip6/dossier/{$dossier->getId()}", 302);
    }
}
