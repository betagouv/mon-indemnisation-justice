<?php

namespace App\Tests\Functional\Requerant;

use App\Entity\Adresse;
use App\Entity\BrisPorte;
use App\Entity\Civilite;
use App\Entity\Document;
use App\Entity\GeoDepartement;
use App\Entity\PersonnePhysique;
use App\Entity\Requerant;
use App\Entity\TestEligibilite;
use App\Tests\Functional\AbstractFunctionalTestCase;
use DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver;
use Doctrine\ORM\EntityManagerInterface;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverDimension;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverPoint;
use GuzzleHttp\Client as HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class DepotBrisPorteTest extends AbstractFunctionalTestCase
{
    protected EntityManagerInterface $em;
    protected UserPasswordHasherInterface $passwordHasher;
    protected HttpClient $mailerClient;

    protected static function pathSuffix(): string
    {
        return 'requerant/depot-dossier';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->passwordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);

        // Création des données de test
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

    protected function getButton(string $label): ?WebDriverElement
    {
        return $this->client->getCrawler()->filter('button,a.fr-btn')
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

        $this->step('Page connexion')
            ->screenshot($device);

        $this->assertSelectorTextContains('main h2', 'Me connecter à mon espace');

        $button = $this->client->getCrawler()->selectButton('Je me connecte à mon espace')->first();
        $form = $button->form([
            '_username' => 'raquel.randt@courriel.fr',
            '_password' => 'P4ssword',
        ]);

        $this->screenshot($device, 'Formulaire rempli');

        $this->assertTrue($button->isEnabled());

        $this->client->submit($form);

        $this->client->waitForVisibility('main', 2);
        $this->assertEquals(Response::HTTP_OK, $this->client->getInternalResponse()->getStatusCode());
        $this->assertSelectorTextContains('main h1', 'Déclarer un bris de porte');

        $this
            ->step('Page données personnelles')
            ->screenshot($device)
            ->setField('Les 10 premiers chiffres de votre numéro de sécurité sociale', '2790656123')
            ->setField('Ville de naissance', 'Turenne')
            ->setField('Pays de naissance', 'France')
            ->setField('Date de naissance', '1979-06-17')
            ->waitDataSaved()
            ->screenshot($device, 'formulaire rempli');

        $this->getButton("Valider et passer à l'étape suivante")->click();
        $this->client->waitForVisibility('main', 2);

        $this
            ->step('Page bris de porte')
            ->setField("Date de l'opération de police judiciaire", date('dmY', strtotime('-1 days')))
            ->setField('Adresse du logement concerné par le bris de porte', '17 rue des oliviers')
            ->setField("Complément d'adresse", 'Escalier B, 3è étage')
            ->setField('Code postal', '13008')
            ->setField('Ville', 'Marseille')
            ->checkField("S'agit-il d'une porte blindée ?", 'Oui')
            ->setField('Vous effectuez votre demande en qualité de', 'Propriétaire')
            ->waitDataSaved()
            ->screenshot($device);

        $this->getButton("Valider et passer à l'étape suivante")->click();

        $this
            ->step('Page pièces jointes')
            ->setField("Attestation complétée par les forces de l'ordre", __DIR__.'/../../ressources/attestation_completee_par_les_forces_de_l_ordre.pdf')
            ->waitDataSaved()
            ->screenshot($device);

        $this->getButton("Valider et passer à l'étape suivante")->click();

        $this
            ->step('Page récapitulatif')
            ->screenshot($device);

        $this->getButton('Je déclare mon bris de porte')->click();

        $this
            ->step('Page mes demandes')
            ->screenshot($device);

        // Il faut purger le cache Doctrine, afin de s'assurer que l'ORM rejoue une requête et récupère l'objet à jour.
        $this->em->clear();
        $requerant = $this->em
            ->getRepository(Requerant::class)
            ->findOneBy(['email' => 'raquel.randt@courriel.fr']);
        $this->assertNotNull($requerant);
        /** @var BrisPorte $dossier */
        $dossier = $requerant->getDossiers()->first();

        $this->assertEquals('2790656123', $requerant->getPersonnePhysique()?->getNumeroSecuriteSociale());
        $this->assertEquals('France', $requerant->getPersonnePhysique()?->getPaysNaissance()->getNom());
        $this->assertEquals('Turenne', $requerant->getPersonnePhysique()?->getCommuneNaissance());
        $this->assertEquals('17-06-1979', $requerant->getPersonnePhysique()?->getDateNaissance()?->format('d-m-Y'));

        $this->assertInstanceOf(BrisPorte::class, $dossier);
        $this->assertCount(1, $dossier->getLiasseDocumentaire()->getDocuments());
        $this->assertEquals(Document::TYPE_ATTESTATION_INFORMATION, $dossier->getLiasseDocumentaire()->getDocuments()->first()->getType());
        $this->assertStringStartsWith('BRI/', $dossier->getReference());
        $this->assertStringEndsWith('/001', $dossier->getReference());
        $this->assertEquals(8, strlen($dossier->getRaccourci()));
    }

    protected function waitDataSaved(): static
    {
        $this->client->wait()->until(self::reactAppSavedChanges());

        return $this;
    }

    private static function reactAppSavedChanges(): callable
    {
        return static function (WebDriver $driver): bool {
            return $driver->executeScript('return !(window.appPendingChanges || false);');
        };
    }
}
