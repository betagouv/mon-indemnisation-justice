<?php

namespace App\Tests\Controller;

use App\Controller\BrisPorteController;
use App\Entity\Adresse;
use App\Entity\Civilite;
use App\Entity\GeoDepartement;
use App\Entity\PersonnePhysique;
use App\Entity\Requerant;
use App\Entity\TestEligibilite;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class BrisPorteControllerTest extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;
    protected UserPasswordHasherInterface $passwordHasher;

    public const REF_TEST_EN_EXPERIMENTATION_COMPLET = 'XP_COMPLET';
    public const REF_TEST_EN_EXPERIMENTATION_INCOMPLET = 'XP_INCOMPLET';
    public const REF_TEST_HORS_EXPERIMENTATION = 'HORS_XP';

    protected array $testsEligibilite = [];

    public function setUp(): void
    {
        $this->client = self::createClient(['debug' => 0]);
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
            ->setEmail('raquel.randt@courriel.fr')
            ->setRoles([Requerant::ROLE_REQUERANT])
        ;
        $requerant->setPassword($this->passwordHasher->hashPassword($requerant, 'P4ssword'));

        $this->em->persist($requerant);

        $departementEnExperimentation = $this->em->getRepository(GeoDepartement::class)->findOneBy(['code' => '35']);
        $departementHorsExperimentation = $this->em->getRepository(GeoDepartement::class)->findOneBy(['code' => '44']);

        $testEnExperimentationComplet = TestEligibilite::fromArray([
            'departement' => $departementEnExperimentation,
            'estVise' => true,
            'requerant' => $requerant,
            'dateSoumission' => (new \DateTime())->modify('-2 minutes'),
        ]);
        $this->em->persist($testEnExperimentationComplet);

        $testEnExperimentationIncomplet = TestEligibilite::fromArray([
            'departement' => $departementEnExperimentation,
            'estVise' => true,
            'dateSoumission' => (new \DateTime())->modify('-2 minutes'),
        ]);
        $this->em->persist($testEnExperimentationIncomplet);

        $testHorsExperimentation = TestEligibilite::fromArray([
            'departement' => $departementHorsExperimentation,
            'estVise' => true,
            'dateSoumission' => (new \DateTime())->modify('-2 minutes'),
        ]);
        $this->em->persist($testHorsExperimentation);
        $this->em->flush();

        $this->testsEligibilite[self::REF_TEST_EN_EXPERIMENTATION_COMPLET] = $testEnExperimentationComplet->id;
        $this->testsEligibilite[self::REF_TEST_EN_EXPERIMENTATION_INCOMPLET] = $testEnExperimentationIncomplet->id;
        $this->testsEligibilite[self::REF_TEST_HORS_EXPERIMENTATION] = $testHorsExperimentation->id;
    }

    protected function initializeSession(array $values = []): void
    {
        $session = $this->client->getContainer()->get('session.factory')->createSession();
        foreach ($values as $key => $value) {
            $session->set($key, $value);
        }

        $session->save();

        $domains = array_unique(array_map(fn (Cookie $cookie) => $cookie->getName() === $session->getName() ? $cookie->getDomain() : '', $this->client->getCookieJar()->all())) ?: [''];
        foreach ($domains as $domain) {
            $cookie = new Cookie($session->getName(), $session->getId(), null, null, $domain);
            $this->client->getCookieJar()->set($cookie);
        }
    }

    public function donnesTesterMonEligibilite()
    {
        return [
            'sans_test' => [null, '/bris-de-porte/creation-de-compte'],
            'test_en_xp_incomplet' => [self::REF_TEST_EN_EXPERIMENTATION_INCOMPLET, '/bris-de-porte/creation-de-compte'],
            'test_en_xp_complet' => [self::REF_TEST_EN_EXPERIMENTATION_COMPLET, '/bris-de-porte/finaliser-la-creation', true],
            'test_hors_xp' => [self::REF_TEST_HORS_EXPERIMENTATION, '/bris-de-porte/creation-de-compte'],
        ];
    }

    /**
     * ETQ visiteur, je dois pouvoir remplir le formulaire de test d'éligibilité.
     *
     * Variantes :
     * * Si j'ai choisi un département hors expérimentation, alors je dois être redirigé vers la page "Contactez-nous"
     * * Si j'ai déjà rempli le questionnaire, alors je dois être automatiquement renvoyé vers la page suivante
     * ("Création de compte" si département en expérimentation, "Contactez-nous" sinon)
     * * Si j'ai déjà rempli mon questionnaire ET créé mon compte, alors je dois être automatiquement renvoyé sur la page
     * "Finaliser la création de votre compte"
     *
     * @dataProvider donnesTesterMonEligibilite
     */
    public function testTesterMonEligibilite(?string $refTestPrecedent = null, ?string $redirection = null, bool $aRequerant = false): void
    {
        if ($refTestPrecedent) {
            $this->initializeSession([BrisPorteController::SESSION_CONTEXT_KEY => $this->testsEligibilite[$refTestPrecedent]]);
        }

        $this->client->request('GET', '/bris-de-porte/tester-mon-eligibilite');

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $form = $this->client->getCrawler()->selectButton("Commencer la demande d'indemnisation")->form();

        $this->client->request($form->getMethod(), $form->getUri(), [
            '_token' => $this->client->getCrawler()->filter('input[name="_token"]')->first()->attr('value'),
            'estIssuAttestation' => 'false',
            'departement' => '77',
            'description' => 'Perquisition pendant mon absence, ce matin',
            'estVise' => 'false',
            'estHebergeant' => 'false',
            'estProprietaire' => 'true',
            'aContacteAssurance' => 'false',
        ]);

        if ($redirection) {
            $this->assertTrue($this->client->getResponse()->isRedirect($redirection));
        }
        $this->em->clear();

        /** @var TestEligibilite $testEligibilite */
        $testEligibilite = $this->em->getRepository(TestEligibilite::class)
            ->createQueryBuilder('t')
            ->orderBy('t.dateSoumission', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
        $this->assertNotNull($testEligibilite);
        $this->assertEquals('77', $testEligibilite->departement->getCode());
        $this->assertEquals('Perquisition pendant mon absence, ce matin', $testEligibilite->description);
        $this->assertNotNull($testEligibilite->dateSoumission);
        $this->assertFalse($testEligibilite->estVise);
        $this->assertFalse($testEligibilite->estHebergeant);
        $this->assertTrue($testEligibilite->estProprietaire);
        $this->assertFalse($testEligibilite->aContacteAssurance);
        if ($aRequerant) {
            $this->assertInstanceOf(Requerant::class, $testEligibilite->requerant);
        } else {
            $this->assertNull($testEligibilite->requerant);
        }

        $this->assertTrue($testEligibilite->estEligibleExperimentation);
    }

    public function donnesContactezNous()
    {
        return [
            'sans_test' => [null, '/bris-de-porte/tester-mon-eligibilite'],
            'test_en_xp_incomplet' => [self::REF_TEST_EN_EXPERIMENTATION_INCOMPLET, '/bris-de-porte/creation-de-compte'],
            'test_en_xp_complet' => [self::REF_TEST_EN_EXPERIMENTATION_COMPLET, '/bris-de-porte/finaliser-la-creation'],
            'test_hors_xp' => [self::REF_TEST_HORS_EXPERIMENTATION],
        ];
    }

    /**
     * ETQ visiteur, après avoir rempli le formulaire de test d'éligibilité, si j'ai choisi un département hors
     * expérimentation, je dois être invité à contacter le bureau per courriel.
     *
     * @dataProvider donnesContactezNous
     */
    public function testContactezNous(?string $refTestPrecedent = null, ?string $redirection = null): void
    {
        if ($refTestPrecedent) {
            $this->initializeSession([BrisPorteController::SESSION_CONTEXT_KEY => $this->testsEligibilite[$refTestPrecedent]]);
        }

        $this->client->request('GET', '/bris-de-porte/contactez-nous');

        if (null !== $redirection) {
            $this->assertTrue($this->client->getResponse()->isRedirect($redirection));
        } else {
            $this->assertTrue($this->client->getResponse()->isSuccessful());
        }
    }

    public function donnesCreationDeCompte()
    {
        return [
            'sans_test' => [null, '/bris-de-porte/tester-mon-eligibilite'],
            'test_en_xp_incomplet' => [self::REF_TEST_EN_EXPERIMENTATION_INCOMPLET],
            'test_en_xp_complet' => [self::REF_TEST_EN_EXPERIMENTATION_COMPLET, '/bris-de-porte/finaliser-la-creation'],
            'test_hors_xp' => [self::REF_TEST_HORS_EXPERIMENTATION, '/bris-de-porte/contactez-nous'],
        ];
    }

    /**
     * ETQ visiteur, après avoir rempli le formulaire de test d'éligibilité, si j'ai choisi un département en
     * expérimentation, je dois être invité à créer mon compte.
     *
     * @dataProvider donnesCreationDeCompte
     */
    public function testCreationDeCompte(?string $refTestPrecedent = null, ?string $redirection = null): void
    {
        if ($refTestPrecedent) {
            $this->initializeSession([BrisPorteController::SESSION_CONTEXT_KEY => $this->testsEligibilite[$refTestPrecedent]]);
        }

        $this->client->request('GET', '/bris-de-porte/creation-de-compte');

        if ($redirection) {
            $this->assertTrue($this->client->getResponse()->isRedirect($redirection));
        } else {
            $this->assertTrue($this->client->getResponse()->isSuccessful());

            $form = $this->client->getCrawler()->selectButton('Valider mon inscription et poursuivre ma demande')->form();

            $this->client->request($form->getMethod(), $form->getUri(), [
                '_token' => $this->client->getCrawler()->filter('input[name="_token"]')->first()->attr('value'),
                'civilite' => 'M',
                'prenom' => 'Rick',
                'nomNaissance' => 'Hérent',
                'nom' => 'Hérent',
                'courriel' => 'rick.herent@courriel.fr',
                'telephone' => '06123456789',
                'motDePasse' => 'P4ssword',
                'confirmation' => 'P4ssword',
            ]);

            $this->assertResponseRedirects('/bris-de-porte/finaliser-la-creation', 302, 'À la soumission du formulaire, je dois être redirigé vers la page de finalisation de la création de compte');
        }
    }

    public function donnesFinaliserLaCreation()
    {
        return [
            'sans_test' => [null, '/bris-de-porte/tester-mon-eligibilite'],
            'test_en_xp_incomplet' => [self::REF_TEST_EN_EXPERIMENTATION_INCOMPLET, '/bris-de-porte/creation-de-compte'],
            'test_en_xp_complet' => [self::REF_TEST_EN_EXPERIMENTATION_COMPLET],
            'test_hors_xp' => [self::REF_TEST_HORS_EXPERIMENTATION, '/bris-de-porte/contactez-nous'],
        ];
    }

    /**
     * ETQ visiteur, après avoir rempli le formulaire de test d'éligibilité et créé mon compte, je dois être avisé que,
     * pour continuer, je dois valider mon adresse en cliquant sur le lien figurant dans le courriel que je viens de
     * recevoir.
     *
     * @dataProvider donnesFinaliserLaCreation
     */
    public function testFinaliserLaCreation(?string $refTestPrecedent = null, ?string $redirection = null): void
    {
        if ($refTestPrecedent) {
            $this->initializeSession([BrisPorteController::SESSION_CONTEXT_KEY => $this->testsEligibilite[$refTestPrecedent]]);
        }

        $this->client->request('GET', '/bris-de-porte/finaliser-la-creation');

        if ($redirection) {
            $this->assertTrue($this->client->getResponse()->isRedirect($redirection), "Je dois être redirigé vers la page '$redirection'");
        } else {
            $this->assertTrue($this->client->getResponse()->isSuccessful(), "Je dois pouvoir consulter la page de finalisation d'inscription");
        }
    }
}
