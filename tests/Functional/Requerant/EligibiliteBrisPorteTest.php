<?php

namespace MonIndemnisationJustice\Tests\Functional\Requerant;

use DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver;
use Doctrine\ORM\EntityManagerInterface;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverDimension;
use Facebook\WebDriver\WebDriverPoint;
use GuzzleHttp\Client as HttpClient;
use MonIndemnisationJustice\Entity\Requerant;
use MonIndemnisationJustice\Tests\Functional\AbstractFunctionalTestCase;
use Symfony\Component\HttpFoundation\Response;

class EligibiliteBrisPorteTest extends AbstractFunctionalTestCase
{
    protected EntityManagerInterface $em;
    protected HttpClient $mailerClient;

    protected static function pathSuffix(): string
    {
        return 'requerant/test-eligibilite';
    }

    protected function setUp(): void
    {
        parent::setUp();

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
        $this->mailerClient = new HttpClient(['base_uri' => $_ENV['MAILPIT_URL'] ?? $_SERVER['MAILPIT_URL']]);
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
    public function testTestEligibiliteEtInscription(string $device, int $width, int $height): void
    {
        $this->markTestSkipped('Délégation à playwright');
        $this->client->getCookieJar()->clear();
        $this->client->manage()->window()
            ->maximize()
            ->setPosition(new WebDriverPoint(0, 0))
            ->setSize(new WebDriverDimension($width, $height))
        ;

        $this->client->request('GET', '/');
        $this->assertEquals(Response::HTTP_OK, $this->client->getInternalResponse()->getStatusCode());

        $this->client->waitForVisibility('main', 1);

        $this
            ->step('Page accueil')
            ->screenshot($device);

        $this->client->clickLink('Déposer votre demande d’indemnisation');
        $this->assertEquals(Response::HTTP_OK, $this->client->getInternalResponse()->getStatusCode());

        $this->client->waitForVisibility('main', 3);

        $this
            ->step("Page test d'éligibilité")
            ->setField('Dans quel département se situe le logement ?', '13 - Bouches-du-Rhône')
            ->wait()
            ->screenshot($device, 'question département')
            ->setField("Vous souhaitez nous apporter des précisions sur l'intervention ?", "Porte fracassée à l'aube");

        $this->client->getCrawler()->selectButton('Passer à la question suivante')->first()?->click();

        $this
            ->wait()
            ->screenshot($device, 'question précisions')
            ->checkField('Étiez-vous la personne recherchée par les forces de l’ordre lors de leur intervention ?', 'Non')
            ->wait()
            ->screenshot($device, 'question est visé')
            ->checkField("Est-ce que la personne recherchée par les forces de l'ordre réside ou est hébergée à l'adresse du logement ayant subi le bris de porte ?", 'Non')
            ->wait()
            ->screenshot($device, 'question est hébergeant')
            ->checkField('Quel est votre statut par rapport au logement ayant subi le bris de porte ?', 'Propriétaire')
            ->wait()
            ->screenshot($device, 'question est propriétaire')
            ->checkField('Avez-vous pris contact avec votre assurance et obtenu une attestation de non prise en charge du sinistre ?', 'Oui')
            ->wait()
            ->screenshot($device, 'question a contacté assurance');

        $button = $this->client->getCrawler()->selectButton("Commencer la demande d'indemnisation")->first();

        $this->assertNotNull($button);
        $button->click();

        $this->assertEquals(Response::HTTP_OK, $this->client->getInternalResponse()->getStatusCode());
        $this->client->waitForVisibility('main', 1);

        $this->client->refreshCrawler();

        $this
            ->step('Page inscription')
            ->screenshot($device);

        $this->getButton("S'inscrire avec une adresse email")->click();

        $this
            ->wait(200)
            ->screenshot($device, 'Formulaire affiché');

        $this->client->refreshCrawler();

        $button = $this->client->getCrawler()->selectButton('Valider mon inscription et poursuivre ma demande');

        $button->form([
            'civilite' => 'M',
            'prenom' => 'Rick',
            'nom' => 'Errant',
            'nomNaissance' => 'Errant',
            'courriel' => 'rick.errant@courriel.fr',
            'telephone' => '0612345678',
            'motDePasse' => 'P4ssword',
            'confirmation' => 'P4ssword',
        ]);

        $this->setField('Je certifie avoir lu et accepté', true);

        $this
            ->screenshot($device, 'Formulaire rempli')
            ->wait(500);

        $this->assertTrue($button->isEnabled());

        $button->click();

        $this->client->waitForVisibility('main', 1);

        $this
            ->screenshot($device, 'Formulaire soumis')
            ->wait(500);

        $this->assertEquals(Response::HTTP_OK, $this->client->getInternalResponse()->getStatusCode());
        $this->assertSelectorTextContains('h2.fr-stepper__title', 'Finaliser la création de votre compte');

        // S'assurer que le requérant a bien reçu un email
        $response = $this->mailerClient->get('/api/v1/search', [
            'query' => [
                'query' => 'to:rick.errant@courriel.fr subject:"finalisation de l\'activation de votre compte pour l\'application"',
            ],
        ]);
        $this->assertEquals(1, json_decode($response->getBody()->getContents(), true)['total']);
    }

    protected function wait(int $duration = 250): static
    {
        $this->client->wait(intervalInMillisecond: $duration)->until(function (WebDriver $driver): bool {
            return true;
        });

        return $this;
    }
}
