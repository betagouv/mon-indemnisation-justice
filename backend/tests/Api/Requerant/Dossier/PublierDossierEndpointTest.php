<?php

namespace MonIndemnisationJustice\Tests\Api\Requerant\Dossier;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Requerant\Dossier\Dto\EtatDossierUsager;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\DossierType;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Entity\PersonnePhysique;
use MonIndemnisationJustice\Entity\Usager;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[CoversClass(PublierDossierEndpointTest::class)]
class PublierDossierEndpointTest extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = self::createClient(['debug' => true]);
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
    }

    public function testPublierDossierOk()
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

        // dump("/api/requerant/dossier/bris-de-porte/$reference}/amender");
        $this->client->loginUser($usager, 'requerant');


        $this->client->request(
            'PATCH',
            "/api/requerant/dossier/bris-de-porte/$reference/publier?_debug",
            content: json_encode(
                ['dateOperation' => '2026-03-03']
            )
        );


        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $donnees = json_decode($this->client->getResponse()->getContent(), true);
        $this->em->refresh($dossier);

        $this->assertEquals(EtatDossierUsager::DEPOSE->value, $donnees['etatActuel']['etat']);
        $this->assertEquals(EtatDossierType::DOSSIER_A_ATTRIBUER, $dossier->getEtatDossier()->getEtat());
        $this->assertInstanceOf(PersonnePhysique::class, $dossier->getRequerantPersonnePhysique());
        $this->assertNull($dossier->getRequerantPersonneMorale());

    }
}
