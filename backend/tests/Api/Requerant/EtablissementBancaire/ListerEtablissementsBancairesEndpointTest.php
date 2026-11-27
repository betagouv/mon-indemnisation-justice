<?php

namespace MonIndemnisationJustice\Tests\Api\Requerant\EtablissementBancaire;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Requerant\EtablissementBancaire\ListerEtablissementsBancairesEndpoint;
use MonIndemnisationJustice\Entity\Usager;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[CoversClass(ListerEtablissementsBancairesEndpoint::class)]
class ListerEtablissementsBancairesEndpointTest extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = self::createClient(['debug' => true]);
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
    }

    public function testListeLesEtablissementsAvecLeurLogo(): void
    {
        $this->client->loginUser($this->em->getRepository(Usager::class)->findOneBy(['email' => 'raquel.randt@courriel.fr']), 'requerant');

        $this->client->request('GET', '/api/requerant/etablissements-bancaires');

        $this->assertResponseIsSuccessful();
        $etablissements = array_column(json_decode($this->client->getResponse()->getContent(), true), null, 'codeBanque');

        $this->assertSame('BNP Paribas', $etablissements['30004']['nom']);
        $this->assertMatchesRegularExpression('#^/images/logos/etablissements_bancaires/[0-9a-f]{16}\.svg$#', $etablissements['30004']['logo']);
        $this->assertFileExists(self::getContainer()->getParameter('kernel.project_dir').'/public'.$etablissements['30004']['logo']);
        $this->assertNull($etablissements['10008']['logo']);
    }

    public function testRefuseLesVisiteursAnonymes(): void
    {
        $this->client->request('GET', '/api/requerant/etablissements-bancaires');

        $this->assertFalse($this->client->getResponse()->isSuccessful());
    }
}
