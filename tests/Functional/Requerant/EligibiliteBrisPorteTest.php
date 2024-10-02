<?php

namespace App\Tests\Functional\Requerant;

use App\Entity\Requerant;
use App\Tests\Functional\AbstractFunctionalTestCase;
use DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver;
use Doctrine\ORM\EntityManagerInterface;
use Facebook\WebDriver\WebDriverDimension;
use Facebook\WebDriver\WebDriverPoint;
use GuzzleHttp\Client as HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Panther\Client as PantherClient;
use Symfony\Component\Panther\DomCrawler\Crawler;
use Symfony\Component\Panther\PantherTestCase;

class EligibiliteBrisPorteTest extends AbstractFunctionalTestCase
{
    protected PantherClient $client;
    protected EntityManagerInterface $em;
    protected HttpClient $mailerClient;

    protected function setUp(): void
    {
        $this->client = static::createPantherClient(
            [
                'browser' => PantherTestCase::FIREFOX,
                'env' => [
                    'APP_ENV' => 'test',
                    'BASE_URL' => 'http://127.0.0.1:9080/',
                ],
            ]);

        // Supprimer les éventuels requérants de test déjà existants :
        $this->em = self::getContainer()->get(EntityManagerInterface::class);

        $requerant = $this->em
            ->getRepository(Requerant::class)
            ->findOneBy(['email' => 'rick.errant@courriel.fr']);

        if (null !== $requerant) {
            $this->em->remove($requerant);
            $this->em->flush();
            // Obligatoire pour contourner le DoctrineTestBundle https://github.com/dmaicher/doctrine-test-bundle?tab=readme-ov-file#debugging
            StaticDriver::commit();
            StaticDriver::beginTransaction();
        }

        // Vider les boîtes courriel :
        // Doc API mailpit https://mailpit.axllent.org/docs/api-v1/view.html#delete-/api/v1/messages
        $this->mailerClient = new HttpClient(['base_uri' => 'http://mailpit:8025']);
        $this->mailerClient->delete('/api/v1/messages');
    }

    // TODO: tester avec différents viewport ($_SERVER['PANTHER_FIREFOX_ARGUMENTS'] = '-width=1200' & $_SERVER['PANTHER_FIREFOX_ARGUMENTS'] = '-width=428')

    /**
     * @dataProvider devices
     *
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeoutException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testDepotDossierBrisPorte(string $device, int $width, int $height): void
    {
        $this->client->getCookieJar()->clear();
        $this->client->manage()->window()
            ->maximize()
            ->setPosition(new WebDriverPoint(0, 0))
            ->setSize(new WebDriverDimension($width, $height))

        ;

        $this->client->request('GET', '/');
        $this->assertEquals(Response::HTTP_OK, $this->client->getInternalResponse()->getStatusCode());

        $this->client->waitForVisibility('main', 1);
        $this->client->takeScreenshot("public/screenshots/test-eligibilite/$device/001-page-accueil.png");

        $this->assertSelectorTextContains('main h1', "Nous vous aidons dans votre demande d'indemnisation");
        $this->client->clickLink('Bris de porte');
        $this->assertEquals(Response::HTTP_OK, $this->client->getInternalResponse()->getStatusCode());

        $crawler = $this->client->waitForVisibility('#page-content', 3);
        $this->client->takeScreenshot("public/screenshots/test-eligibilite/$device/002-test-eligibilite.png");

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
        $crawler = $this->client->waitForVisibility('dialog', 1);

        $this->client->takeScreenshot("public/screenshots/test-eligibilite/$device/003-modale-formulaire.png");

        $button = $crawler->selectButton("Vérifier mon éligibilité à l'indemnisation")->first();
        $this->assertNotNull($button);
        $form = $button->form([
            'dateOperationPJ' => '2023-12-31',
            'numeroPV' => 'PV44',
            'isErreurPorte' => 'true',
        ]);

        $this->client->takeScreenshot("public/screenshots/test-eligibilite/$device/004-modale-formulaire-rempli.png");

        $this->client->submit($form);

        // $crawler = $this->client->waitForVisibility('dialog#modale-test-eligibilite h3', 2);
        $this->client->takeScreenshot("public/screenshots/test-eligibilite/$device/005-modale-formulaire-soumis.png");

        $this->assertNotNull(
            $this->client
                ->getCrawler()
                ->filter('dialog h3.fr-alert__title')
                ->reduce(function ($e) {
                    return $e->isDisplayed() && "Vous êtes éligible à l'indemnisation";
                })
                ->first()
        );

        $button = $this->client->getCrawler()->selectButton("Accéder au formulaire de demande d'indemnisation")->first();
        $this->assertNotNull($button);
        $button->click();
        $this->assertEquals(Response::HTTP_OK, $this->client->getInternalResponse()->getStatusCode());
        $this->client->waitForVisibility('main', 1);

        $this->client->takeScreenshot("public/screenshots/test-eligibilite/$device/006-page-inscription.png");

        $button = $this->client->getCrawler()->selectButton('Valider mon inscription et poursuivre ma demande')->first();
        $form = $button->form([
            'civilite' => 'M',
            'prenom' => 'Rick',
            'nom' => 'Errant',
            'courriel' => 'rick.errant@courriel.fr',
            'motDePasse' => 'P4ssword',
            'confirmation' => 'P4ssword',
            'cguOk' => true,
        ]);

        $this->client->takeScreenshot("public/screenshots/test-eligibilite/$device/007-inscription-formulaire-rempli.png");
        sleep(1);
        $this->assertTrue($button->isEnabled());
        $this->client->submit($form);

        $this->client->waitForVisibility('main', 1);
        $this->client->takeScreenshot("public/screenshots/test-eligibilite/$device/008-inscription-formulaire-soumis.png");

        $this->assertEquals(Response::HTTP_OK, $this->client->getInternalResponse()->getStatusCode());
        $this->assertSelectorTextContains('main h1', 'Finaliser la création de votre compte');

        // S'assurer que le requérant a bien reçu un email
        $response = $this->mailerClient->get('/api/v1/search', [
            'query' => [
                'query' => 'to:rick.errant@courriel.fr subject:"finalisation de l\'activation de votre compte pour l\'application"',
            ],
        ]);
        $this->assertEquals(1, json_decode($response->getBody()->getContents(), true)['total']);
    }
}
