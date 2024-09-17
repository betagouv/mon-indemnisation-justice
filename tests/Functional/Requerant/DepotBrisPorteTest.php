<?php

namespace App\Tests\Functional\Requerant;

use App\Entity\Requerant;
use DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver;
use Doctrine\ORM\EntityManagerInterface;

use GuzzleHttp\Client as HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Panther\DomCrawler\Crawler;
use Symfony\Component\Panther\PantherTestCase;

class DepotBrisPorteTest extends PantherTestCase
{
    protected EntityManagerInterface $em;
    protected HttpClient $mailerClient;

    protected function setUp(): void
    {
        // Supprimer les éventuels requérants de test déjà existants :
        $this->em = self::getContainer()->get(EntityManagerInterface::class);

        $requerant = $this->em
            ->getRepository(Requerant::class)
            ->findOneBy(['email' => 'rick.errant@truc.fr']);

        if (null !== $requerant) {
            $this->em->remove($requerant);
            $this->em->flush();
            // Obligatoire pour contourner le DoctrineTestBundle https://github.com/dmaicher/doctrine-test-bundle?tab=readme-ov-file#debugging
            StaticDriver::commit();
        }

        // Vider les boîtes courriel :
        // Doc API mailpit https://mailpit.axllent.org/docs/api-v1/view.html#delete-/api/v1/messages
        $this->mailerClient = new HttpClient(['base_uri' => 'http://mailpit:8025']);
        $this->mailerClient->delete('/api/v1/messages');
    }

    public function testDepotDossierBrisPorte(): void
    {
        // your app is automatically started using the built-in web server
        $client = static::createPantherClient(
            [
                'browser' => PantherTestCase::FIREFOX,
                'env' => [
                    'APP_ENV' => 'test',
                    'BASE_URL' => 'http://127.0.0.1:9080/',
                ],
            ],
            [],
            [
                'capabilities' => [
                    'goog:loggingPrefs' => [
                        'browser' => 'ALL', // calls to console.* methods
                        'performance' => 'ALL', // performance data
                    ],
                ],
            ]);
        $client->request('GET', '/');
        $this->assertEquals(Response::HTTP_OK, $client->getInternalResponse()->getStatusCode());

        $client->waitForVisibility('main', 1);
        $client->takeScreenshot('var/screenshots/001-page-accueil.png');

        $this->assertSelectorTextContains('main h1', "Nous vous aidons dans votre demande d'indemnisation");
        $client->clickLink('Bris de porte');
        $this->assertEquals(Response::HTTP_OK, $client->getInternalResponse()->getStatusCode());

        $crawler = $client->waitForVisibility('#page-content', 3);
        $client->takeScreenshot('var/screenshots/002-test-eligibilite.png');

        $this->assertSelectorTextContains('#page-content h1', 'Bris de porte');
        // Il ne doit pas y avoir de modales ouvertes
        $this->assertSelectorIsNotVisible('dialog');

        $button = $crawler
            ->filter('button')
            ->reduce(function (Crawler $node, $i): bool {
                return "Tester mon éligibilité à l'indemnisation" === trim($node->text());
            })
            ->first();
        $this->assertNotNull($button);
        $button->click();

        // La modale doit apparaître
        $crawler = $client->waitForVisibility('dialog', 1);

        $client->takeScreenshot('var/screenshots/003-modale-formulaire.png');

        $button = $crawler->selectButton("Vérifier mon éligibilité à l'indemnisation")->first();
        $this->assertNotNull($button);
        $form = $button->form([
            'dateOperationPJ' => '2023-12-31',
            'numeroPV' => 'PV44',
            'isErreurPorte' => 'true',
        ]);

        $client->takeScreenshot('var/screenshots/004-modale-formulaire-rempli.png');

        $client->submit($form);

        // $crawler = $client->waitForVisibility('dialog#modale-test-eligibilite h3', 2);
        $client->takeScreenshot('var/screenshots/005-modale-formulaire-soumis.png');

        $this->assertNotNull(
            $client
                ->getCrawler()
                ->filter('dialog h3.fr-alert__title')
                ->reduce(function ($e) {
                    return $e->isDisplayed() && "Vous êtes éligible à l'indemnisation";
                })
                ->first()
        );

        $button = $client->getCrawler()->selectButton("Accéder au formulaire de demande d'indemnisation")->first();
        $this->assertNotNull($button);
        $button->click();
        $this->assertEquals(Response::HTTP_OK, $client->getInternalResponse()->getStatusCode());
        $client->waitForVisibility('main', 1);

        $client->takeScreenshot('var/screenshots/006-page-inscription.png');

        $button = $client->getCrawler()->selectButton('Valider mon inscription et poursuivre ma demande')->first();
        $form = $button->form([
            'civilite' => 'M',
            'prenom1' => 'Rick',
            'nomNaissance' => 'Errant',
            'email' => 'rick.errant@truc.fr',
            'password' => 'P4ssword',
            'confirm' => 'P4ssword',
            'cgu' => 'true',
        ]);

        $client->takeScreenshot('var/screenshots/007-inscription-formulaire-rempli.png');
        sleep(1);
        $this->assertTrue($button->isEnabled());
        $client->submit($form);

        $client->waitForVisibility('main', 1);
        $client->takeScreenshot('var/screenshots/008-inscription-formulaire-soumis.png');

        $this->assertEquals(Response::HTTP_OK, $client->getInternalResponse()->getStatusCode());
        $this->assertSelectorTextContains('main h1', 'Finaliser la création de votre compte');

        // S'assurer que le requérant a bien reçu un email
        $response = $this->mailerClient->get('/api/v1/search', [
            'query' => [
                'query' => 'to:rick.errant@truc.fr subject:"finalisation de l\'activation de votre compte pour l\'application"',
            ]
        ]);
        $this->assertEquals(1, json_decode($response->getBody()->getContents(), true)["total"]);
    }
}
