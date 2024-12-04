<?php

namespace App\Tests\Functional\Requerant;

use App\Entity\Adresse;
use App\Entity\BrisPorte;
use App\Entity\Civilite;
use App\Entity\GeoDepartement;
use App\Entity\PersonnePhysique;
use App\Entity\Requerant;
use App\Entity\TestEligibilite;
use App\Tests\Functional\AbstractFunctionalTestCase;
use DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver;
use Doctrine\ORM\EntityManagerInterface;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverDimension;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverPoint;
use GuzzleHttp\Client as HttpClient;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Panther\Client as PantherClient;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class DepotBrisPorteTest extends AbstractFunctionalTestCase
{
    protected PantherClient $client;
    protected EntityManagerInterface $em;
    protected UserPasswordHasherInterface $passwordHasher;
    protected HttpClient $mailerClient;
    protected string $screenShotDir;

    protected function setUp(): void
    {
        // See config https://hacks.mozilla.org/2017/12/using-headless-mode-in-firefox/
        $this->client = static::createPantherClient(
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
            ]
        );

        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->passwordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        $this->screenShotDir = self::getContainer()->getParameter('kernel.project_dir').'/public/screenshots';
        $finder = new Finder();
        $filesystem = new Filesystem();
        if ($filesystem->exists($this->screenShotDir)) {
            $filesystem->remove($finder->directories()->in($this->screenShotDir));
        }

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
            ->setEmail('raquel.randt@courriel.fr')
            ->setRoles([Requerant::ROLE_REQUERANT])
        ;
        $requerant->setPassword($this->passwordHasher->hashPassword($requerant, 'P4ssword'));

        $testEligibilite = new TestEligibilite();
        $testEligibilite->departement = $this->em->getRepository(GeoDepartement::class)->findOneBy(['code' => '13']);
        $testEligibilite->estVise = false;
        $testEligibilite->estHebergeant = false;
        $testEligibilite->estProprietaire = true;
        $testEligibilite->aContacteAssurance = false;

        $dossier = (new BrisPorte())->setRequerant($requerant)->setTestEligibilite($testEligibilite);

        $this->em->persist($testEligibilite);
        $this->em->persist($dossier);
        $this->em->persist($requerant);
        $this->em->flush();
        // Obligatoire pour contourner le DoctrineTestBundle https://github.com/dmaicher/doctrine-test-bundle?tab=readme-ov-file#debugging
        StaticDriver::commit();
        StaticDriver::beginTransaction();
    }

    protected function getFieldByLabel(string $label, bool $exactMatch = false): ?WebDriverElement
    {
        $label = $this->client->getCrawler()->filter('label')
                ->reduce(function (WebDriverElement $e) use ($label, $exactMatch) {
                    if ($exactMatch) {
                        return trim($e->getText()) === $label;
                    }

                    return str_contains($e->getText(), $label);
                })
                ->first() ?? null;

        if ($label && $label->getAttribute('for')) {
            $target = $this->client->getCrawler()->findElement(WebDriverBy::id($label->getAttribute('for'))) ?? null;

            if (null === $target) {
                throw new \LogicException("No form field found with id {$label->getAttribute('for')}");
            }

            if (!in_array($target->getTagName(), ['input', 'select', 'textarea'])) {
                throw new \LogicException('Target element is not a form field (<input>, <select> or  <textarea>)');
            }

            return $target;
        }

        return null;
    }

    protected function getButton(string $label): ?WebDriverElement
    {
        return $this->client->getCrawler()->filter('button')
                ->reduce(function (WebDriverElement $e) use ($label) {
                    return trim($e->getText()) === $label;
                })
                ->first() ?? null;
    }

    /**
     * @dataProvider devices
     *
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeoutException
     */
    public function testDepotDossierBrisPorte(string $device, int $width, int $height): void
    {
        $this->client->getCookieJar()->clear();
        $this->client->manage()->window()
            ->maximize()
            ->setPosition(new WebDriverPoint(0, 0))
            ->setSize(new WebDriverDimension($width, $height))

        ;

        $this->client->get('/connexion');
        $this->assertEquals(Response::HTTP_OK, $this->client->getInternalResponse()->getStatusCode());

        $this->client->waitForVisibility('main', 1);
        $this->client->takeScreenshot("$this->screenShotDir/$device/001-page-connexion.png");
        $this->assertSelectorTextContains('main h2', 'Me connecter à mon espace');
        $button = $this->client->getCrawler()->selectButton('Je me connecte à mon espace')->first();
        $form = $button->form([
            '_username' => 'raquel.randt@courriel.fr',
            '_password' => 'P4ssword',
        ]);

        $this->client->takeScreenshot("$this->screenShotDir/$device/002-connexion-formulaire-rempli.png");

        $this->assertTrue($button->isEnabled());
        $this->client->submit($form);
        $this->client->takeScreenshot("$this->screenShotDir/$device/003-connexion-formulaire-soumis.png");

        $this->client->waitForVisibility('main', 2);
        $this->client->takeScreenshot("$this->screenShotDir/$device/004-page-accueil-requerant.png");

        $this->assertEquals(Response::HTTP_OK, $this->client->getInternalResponse()->getStatusCode());
        $this->assertSelectorTextContains('main h1', 'Déclarer un bris de porte');

        $input = $this->getFieldByLabel('Les 10 premiers chiffres de votre numéro de sécurité sociale');

        $input->clear();
        $input->sendKeys('2790656123');
        // Astuce pour s'assurer que la requête xhr de `PATCH` ait bien été déclenchée :
        // TODO voir pour observer les `queuedChanges` par exemple https://github.com/php-webdriver/php-webdriver/wiki/How-to-work-with-AJAX-(jQuery,-Prototype,-Dojo)
        sleep(1);
        // Attendre pour s'assurer que les données ont bien été transmises à l'API
        $this->client->takeScreenshot("$this->screenShotDir/$device/005-page-donnees-personnelles.png");

        $this->getButton("Valider et passer à l'étape suivante")->click();

        $this->client->takeScreenshot("$this->screenShotDir/$device/006-page-donnees-bris-de-porte.png");

        // Il faut purger le cache Doctrine, afin de s'assurer que l'ORM rejoue une requête et récupère l'objet à jour
        $this->em->clear();
        $requerant = $this->em
            ->getRepository(Requerant::class)
            ->findOneBy(['email' => 'raquel.randt@courriel.fr']);
        $this->assertNotNull($requerant);
        $this->assertEquals('2790656123', $requerant->getPersonnePhysique()?->getNumeroSecuriteSociale());
    }
}
