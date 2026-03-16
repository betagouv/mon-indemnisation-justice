<?php

namespace MonIndemnisationJustice\Tests\Api\Requerant\Dossier;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Requerant\Dossier\AmenderDossierEndpoint;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\DossierType;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Entity\PersonnePhysique;
use MonIndemnisationJustice\Entity\Usager;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[CoversClass(AmenderDossierEndpoint::class)]
class AmenderDossierEndpointTest extends WebTestCase
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
            'PATCH',
            "/api/requerant/dossier/bris-de-porte/$reference/amender",
            content: json_encode(
                [
                    'adresse' => [
                        'ligne1' => '17 boulevard Pereire',
                        'ligne2' => null,
                        'codePostal' => '75017',
                        'commune' => 'Paris',
                    ],
                    'descriptionRapportAuLogement' => '',
                    'dateOperation' => '2026-03-03',

                    'personnePhysique' => [
                        'personne' => [
                            'prenom' => 'Jacques',
                            'nom' => 'CHIRAC',
                        ],
                        'villeNaissance' => 'Oran',
                    ],
                ]
            )
        );

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $donnees = json_decode($this->client->getResponse()->getContent(), true);

        $this->em->refresh($dossier);
        $this->assertEquals('17 boulevard Pereire', $dossier->getBrisPorte()->getAdresse()->getLigne1());
        $this->assertInstanceOf(PersonnePhysique::class, $dossier->getRequerantPersonnePhysique());
        $this->assertEquals('38053', $dossier->getRequerantPersonnePhysique()->getCodePostalNaissance()->getCommune()->getCode());
        $this->assertEquals('Oran', $dossier->getRequerantPersonnePhysique()->getVilleNaissance());
        $this->assertEquals('Jacques', $dossier->getRequerantPersonnePhysique()->getPersonne()->getPrenom());
        $this->assertEquals('CHIRAC', $dossier->getRequerantPersonnePhysique()->getPersonne()->getNom());
        $this->assertEquals('0621436587', $dossier->getRequerantPersonnePhysique()->getPersonne()->getTelephone());
        $this->assertIsArray($donnees['personnePhysique']);
    }
}
