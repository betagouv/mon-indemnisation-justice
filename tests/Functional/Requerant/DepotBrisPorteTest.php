<?php

namespace App\Tests\Functional\Requerant;

use App\Entity\Adresse;
use App\Entity\Civilite;
use App\Entity\PersonnePhysique;
use App\Entity\Requerant;
use DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client as HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Panther\Client as PantherClient;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class DepotBrisPorteTest extends PantherTestCase
{
    protected PantherClient $client;
    protected EntityManagerInterface $em;
    protected UserPasswordHasherInterface $passwordHasher;
    protected HttpClient $mailerClient;

    protected function setUp(): void
    {
        // your app is automatically started using the built-in web server
        $this->client = static::createPantherClient(
            [
                'browser' => PantherTestCase::FIREFOX,
                'env' => [
                    'APP_ENV' => 'test',
                    'BASE_URL' => 'http://127.0.0.1:9080/',
                ],
            ]
        );
        $this->client->getCookieJar()->clear();

        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->passwordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);

        $requerant = $this->em
            ->getRepository(Requerant::class)
            ->findOneBy(['email' => 'raquel.randt@courriel.fr']);

        if (null !== $requerant) {
            $this->em->remove($requerant);
            $this->em->flush();
        }

        $requerant = (new Requerant())
            ->setAdresse(
                (new Adresse())
                ->setLigne1('12 rue des Oliviers')
                ->setLocalite('Nantes')
                ->setCodePostal('44100')
            )
            ->setPersonnePhysique(
                (new PersonnePhysique())
                ->setEmail('raquel.randt@courriel.fr')
                ->setCivilite(Civilite::MME)
                ->setPrenom1('Raquel')
                ->setNom('Randt')
            )
            ->setVerifieCourriel()
            ->setTestEligibilite([
                "dateOperationPJ" => "2023-12-31",
                "numeroPV" => "PV44",
                "numeroParquet" =>  "",
                "isErreurPorte" => true
            ])
            ->setEmail('raquel.randt@courriel.fr')
            ->setRoles([Requerant::ROLE_REQUERANT])
        ;
        $requerant->setPassword($this->passwordHasher->hashPassword($requerant, 'P4ssword'));

        $this->em->persist($requerant);
        $this->em->flush();
        // Obligatoire pour contourner le DoctrineTestBundle https://github.com/dmaicher/doctrine-test-bundle?tab=readme-ov-file#debugging
        StaticDriver::commit();
        StaticDriver::beginTransaction();
    }

    public function testDepotDossierBrisPorte(): void
    {
        $this->client->get('/connexion');
        $this->assertEquals(Response::HTTP_OK, $this->client->getInternalResponse()->getStatusCode());

        $this->client->waitForVisibility('main', 1);
        $this->client->takeScreenshot('public/screenshots/depot/001-page-connexion.png');
        $this->assertSelectorTextContains('main h2', "Me connecter à mon espace");
        $button = $this->client->getCrawler()->selectButton('Je me connecte à mon espace')->first();
        $form = $button->form([
            'email' => 'raquel.randt@courriel.fr',
            'password' => 'P4ssword',
        ]);

        $this->client->takeScreenshot('public/screenshots/depot/002-connexion-formulaire-rempli.png');
        sleep(1);
        $this->assertTrue($button->isEnabled());
        $this->client->submit($form);
        $this->client->takeScreenshot('public/screenshots/depot/003-connexion-formulaire-soumis.png');

        $this->client->waitForVisibility('main', 2);
        $this->client->takeScreenshot('public/screenshots/depot/004-page-accueil-requerant.png');

        $this->assertEquals(Response::HTTP_OK, $this->client->getInternalResponse()->getStatusCode());
        $this->assertSelectorTextContains('main h1', 'Déclarer un bris de porte');
    }
}
