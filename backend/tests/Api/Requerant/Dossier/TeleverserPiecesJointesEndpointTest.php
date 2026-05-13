<?php

namespace MonIndemnisationJustice\Tests\Api\Requerant\Dossier;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Requerant\Dossier\AmenderDossierEndpoint;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\DossierType;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Entity\Usager;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[CoversClass(AmenderDossierEndpoint::class)]
class TeleverserPiecesJointesEndpointTest extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = self::createClient(['debug' => true]);
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
    }

    public function testTeleverserPiecesJointesOk()
    {
        $usager = $this->em->getRepository(Usager::class)->findOneBy(['email' => 'raquel.randt@courriel.fr']);
        /** @var Dossier $dossier */
        $dossier = $usager->getDossiersBrisDePorte()
            ->filter(
                fn (Dossier $d) => DossierType::BRIS_PORTE === $d->getType()
                    && EtatDossierType::DOSSIER_A_FINALISER === $d->getEtatDossier()->getEtat()
            )
            ->first();
        $reference = $dossier->getReference() ?? $dossier->getId();
        $this->client->loginUser($usager, 'requerant');

        $this->client->request(
            'POST',
            "/api/requerant/dossier/bris-de-porte/$reference/televerser-pieces-jointes",
            parameters: [
                'donnees' => json_encode([
                    ['type' => DocumentType::TYPE_PHOTO_PREJUDICE],
                    ['type' => DocumentType::TYPE_FACTURE, 'contexte' => ['montant' => 1234.56]],
                ]),
            ],
            files: [
                'piecesJointes' => [
                    new UploadedFile(dirname(__FILE__).'/../../../ressources/pieces_jointes/photo-1.jpg', 'photo-1.jpg', 'image/jpeg'),
                    new UploadedFile(dirname(__FILE__).'/../../../ressources/pieces_jointes/Facture 2.pdf', 'Facture 2.pdf', 'application/pdf'),
                ],
            ]
        );

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $donnees = json_decode($this->client->getResponse()->getContent(), true);

        $this->em->refresh($dossier);

        $this->assertCount(2, $dossier->getPiecesJointes());
    }
}
