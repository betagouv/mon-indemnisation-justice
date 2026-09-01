<?php

namespace MonIndemnisationJustice\Tests\Api\Requerant\Dossier;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\TestEligibiliteDysfonctionnement;
use MonIndemnisationJustice\Entity\Usager;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[CoversClass(\MonIndemnisationJustice\Api\Requerant\Dossier\TeleverserPieceIdentiteDysfonctionnementEndpoint::class)]
class TeleverserPieceIdentiteDysfonctionnementEndpointTest extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
    }

    public function testTeleverserLaPieceDIdentite(): void
    {
        $usager = $this->em->getRepository(Usager::class)->findOneBy(['email' => 'raquel.randt@courriel.fr']);

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

        $fichier = tempnam(sys_get_temp_dir(), 'piece').'.pdf';
        file_put_contents($fichier, '%PDF-1.4 contenu de test');
        $upload = new UploadedFile($fichier, 'carte_identite.pdf', 'application/pdf', null, true);

        $this->client->request(
            'POST',
            "/api/requerant/dossier/dysfonctionnement/{$dossier->getId()}/televerser-pieces-jointes",
            ['donnees' => json_encode([['type' => 'carte_identite']])],
            ['piecesJointes' => [$upload]]
        );

        $this->assertResponseIsSuccessful();
        $donnees = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($donnees['pieceIdentiteDeposee']);

        unlink($fichier);
    }
}
