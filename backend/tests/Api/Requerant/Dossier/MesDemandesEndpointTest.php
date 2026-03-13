<?php

namespace MonIndemnisationJustice\Tests\Api\Requerant\Dossier;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Requerant\Dossier\Dto\EtatDossierUsager;
use MonIndemnisationJustice\Api\Requerant\Dossier\MesDemandesEndpoint;
use MonIndemnisationJustice\Entity\Usager;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[CoversClass(MesDemandesEndpoint::class)]
class MesDemandesEndpointTest extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = self::createClient(['debug' => true]);
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
    }

    public function testAmenderDossierOk()
    {
        $usager = $this->em->getRepository(Usager::class)->findOneBy(['email' => 'wossewodda-3728@yopmail.com']);
        $this->client->loginUser($usager, 'requerant');


        $this->client->request(
            'GET',
            '/api/requerant/mes-demandes'
        );

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $donnees = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(1, $donnees);
        $this->assertEquals(EtatDossierUsager::A_COMPLETER->value, $donnees[0]['etatActuel']['etat']);
    }
}
