<?php

namespace MonIndemnisationJustice\Tests\Controller;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Controller\BrisPorteController;
use MonIndemnisationJustice\Entity\Requerant;
use MonIndemnisationJustice\Entity\TestEligibilite;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;

class BrisPorteControllerTest extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;

    public function setUp(): void
    {
        $this->client = self::createClient(['debug' => 0]);
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
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

    protected function getTestEligibiliteEnXpComplet(): callable
    {
        return function (EntityManagerInterface $em) {
            $test = TestEligibilite::fromArray([
                'description' => 'Test complet',
                'estVise' => true,
                'requerant' => $em->getRepository(Requerant::class)->findOneBy(['email' => 'raquel.randt@courriel.fr']),
                'dateSoumission' => (new \DateTime())->modify('-2 minutes')]);

            $em->persist($test);
            $em->flush();

            return $test;
        };
    }

    protected function getTestEligibiliteEnXpIncomplet(): callable
    {
        return function (EntityManagerInterface $em) {
            $test = TestEligibilite::fromArray([
                'description' => 'Test incomplet',
                'estVise' => true,
                'dateSoumission' => (new \DateTime())->modify('-2 minutes')]);

            $em->persist($test);
            $em->flush();

            return $test;
        };
    }

    public function donnesTesterMonEligibilite()
    {
        return [
            'sans_test' => [null, '/bris-de-porte/creation-de-compte'],
            'test_incomplet' => [$this->getTestEligibiliteEnXpIncomplet(), '/bris-de-porte/creation-de-compte'],
            'test_complet' => [$this->getTestEligibiliteEnXpComplet(), '/bris-de-porte/finaliser-la-creation', true],
        ];
    }

    /**
     * ETQ visiteur, je dois pouvoir remplir le formulaire de test d'éligibilité.
     *
     * Variantes :
     * * Si j'ai déjà rempli le questionnaire, alors je dois être automatiquement renvoyé vers la page suivante
     * * Si j'ai déjà rempli mon questionnaire ET créé mon compte, alors je dois être automatiquement renvoyé sur la page
     * "Finaliser la création de votre compte"
     *
     * @dataProvider donnesTesterMonEligibilite
     */
    public function testTesterMonEligibilite(?callable $getTestEligibilite = null, ?string $redirection = null, bool $aRequerant = false): void
    {
        if ($getTestEligibilite) {
            /** @var TestEligibilite $testEligibilite */
            $testEligibilite = $getTestEligibilite($this->em);
            $this->initializeSession([BrisPorteController::SESSION_CONTEXT_KEY => $testEligibilite->id]);
        }

        $this->client->request('GET', '/bris-de-porte/tester-mon-eligibilite');

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $reactArgs = json_decode($this->client->getCrawler()->filter('#react-arguments')->first()->innerText());

        $this->client->request('POST', '/bris-de-porte/tester-mon-eligibilite', [
            '_token' => $reactArgs->_token,
            'estIssuAttestation' => 'false',
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

    public function donnesCreationDeCompte()
    {
        return [
            'sans_test' => [null, '/bris-de-porte/tester-mon-eligibilite'],
            'test_incomplet' => [$this->getTestEligibiliteEnXpIncomplet()],
            'test_complet' => [$this->getTestEligibiliteEnXpComplet(), '/bris-de-porte/finaliser-la-creation'],
        ];
    }

    /**
     * ETQ visiteur, après avoir rempli le formulaire de test d'éligibilité, si j'ai choisi un département en
     * expérimentation, je dois être invité à créer mon compte.
     *
     * @dataProvider donnesCreationDeCompte
     */
    public function testCreationDeCompte(?callable $getTestEligibilite = null, ?string $redirection = null): void
    {
        if ($getTestEligibilite) {
            /** @var TestEligibilite $testEligibilite */
            $testEligibilite = $getTestEligibilite($this->em);
            $this->initializeSession([BrisPorteController::SESSION_CONTEXT_KEY => $testEligibilite->id]);
        }

        $this->client->request('GET', '/bris-de-porte/creation-de-compte');
        if ($redirection) {
            $this->assertTrue($this->client->getResponse()->isRedirect($redirection));
        } else {
            $this->assertTrue($this->client->getResponse()->isSuccessful());

            $reactArgs = json_decode(trim($this->client->getCrawler()->filter('#react-arguments')->first()->text()), true);
            $this->assertIsArray($reactArgs);
            $token = $reactArgs['token'];
            $this->assertNotEmpty($token);

            $this->client->request('POST', '/bris-de-porte/creer-compte', [
                'cguOk' => true,
                'civilite' => 'M',
                'prenom' => 'Rick',
                'nomNaissance' => 'Hérent',
                'nom' => 'Hérent',
                'courriel' => 'rick.herent@courriel.fr',
                'telephone' => '06123456789',
                'motDePasse' => 'P4ssword',
                'confirmation' => 'P4ssword',
            ], [], [
                'HTTP_X-Csrf-Token' => $token,
            ]);

            $this->client->request('GET', '/bris-de-porte/creation-de-compte');

            $this->assertResponseRedirects('/bris-de-porte/finaliser-la-creation', 302, 'À la soumission du formulaire, je dois être redirigé vers la page de finalisation de la création de compte');
        }
    }

    public function donnesFinaliserLaCreation()
    {
        return [
            'sans_test' => [null, '/bris-de-porte/tester-mon-eligibilite'],
            'test_incomplet' => [$this->getTestEligibiliteEnXpIncomplet(), '/bris-de-porte/creation-de-compte'],
            'test_complet' => [$this->getTestEligibiliteEnXpComplet()],
        ];
    }

    /**
     * ETQ visiteur, après avoir rempli le formulaire de test d'éligibilité et créé mon compte, je dois être avisé que,
     * pour continuer, je dois valider mon adresse en cliquant sur le lien figurant dans le courriel que je viens de
     * recevoir.
     *
     * @dataProvider donnesFinaliserLaCreation
     */
    public function testFinaliserLaCreation(?callable $getTestEligibilite = null, ?string $redirection = null): void
    {
        if ($getTestEligibilite) {
            /** @var TestEligibilite $testEligibilite */
            $testEligibilite = $getTestEligibilite($this->em);
            $this->initializeSession([BrisPorteController::SESSION_CONTEXT_KEY => $testEligibilite->id]);
        }

        $this->client->request('GET', '/bris-de-porte/finaliser-la-creation');

        if ($redirection) {
            $this->assertTrue($this->client->getResponse()->isRedirect($redirection), "Je dois être redirigé vers la page '$redirection'");
        } else {
            $this->assertTrue($this->client->getResponse()->isSuccessful(), "Je dois pouvoir consulter la page de finalisation d'inscription");
        }
    }
}
