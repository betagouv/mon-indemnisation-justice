<?php

namespace MonIndemnisationJustice\Tests\Controller;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Controller\BrisPorteController;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use MonIndemnisationJustice\Entity\RapportAuLogement;
use MonIndemnisationJustice\Entity\TestEligibiliteBrisPorte;
use MonIndemnisationJustice\Entity\Usager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @internal
 */
#[CoversClass(BrisPorteController::class)]
class BrisPorteControllerTest extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;

    public function setUp(): void
    {
        $this->client = self::createClient(['debug' => 0]);
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
    }

    /**
     * ETQ visiteur, je dois pouvoir remplir le formulaire de test d'éligibilité.
     *
     * Variantes :
     * * Si j'ai déjà rempli le questionnaire, alors je dois être automatiquement renvoyé vers la page suivante
     * * Si j'ai déjà rempli mon questionnaire ET créé mon compte, alors je dois être automatiquement renvoyé sur la page
     * "Finaliser la création de votre compte"
     */
    #[DataProvider('donneesTesterMonEligibilite')]
    public function testTesterMonEligibilite(?callable $getTestEligibilite = null, ?string $redirection = null, bool $aRequerant = false): void
    {
        if ($getTestEligibilite) {
            /** @var TestEligibiliteBrisPorte $testEligibilite */
            $testEligibilite = $getTestEligibilite($this->em);
            $this->initializeSession([BrisPorteController::CLEF_SESSION_TEST_ELIGIBILITE => $testEligibilite->id]);
        }

        $this->client->request('GET', '/bris-de-porte/tester-mon-eligibilite');


        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $reactArgs = json_decode($this->client->getCrawler()->filter('#react-arguments')->first()->innerText());

        $this->client->request('POST', '/bris-de-porte/tester-mon-eligibilite', [
            '_token' => $reactArgs->_token,
            'estIssuAttestation' => 'false',
            // 'description' => 'Perquisition pendant mon absence, ce matin',
            'rapportAuLogement' => 'PROPRIETAIRE',
            'estVise' => 'false',
            'estHebergeant' => 'false',
            'aContacteAssurance' => 'false',
        ]);

        if ($redirection) {
            $this->assertTrue($this->client->getResponse()->isRedirect($redirection));
        }
        $this->em->clear();

        /** @var TestEligibiliteBrisPorte $testEligibilite */
        $testEligibilite = $this->em->getRepository(TestEligibiliteBrisPorte::class)
            ->createQueryBuilder('t')
            ->orderBy('t.dateSoumission', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
        $this->assertNotNull($testEligibilite);
        $this->assertNotNull($testEligibilite->dateSoumission);
        $this->assertFalse($testEligibilite->estVise);
        $this->assertFalse($testEligibilite->estHebergeant);
        $this->assertEquals(RapportAuLogement::PROPRIETAIRE, $testEligibilite->rapportAuLogement);
        $this->assertFalse($testEligibilite->aContacteAssurance);
        if ($aRequerant) {
            $this->assertInstanceOf(Usager::class, $testEligibilite->usager);
        } else {
            $this->assertNull($testEligibilite->usager);
        }

        $this->assertTrue($testEligibilite->estEligibleExperimentation);
    }

    public static function donneesTesterMonEligibilite()
    {
        return [
            'sans_test' => [null, '/bris-de-porte/creation-de-compte'],
            'test_incomplet' => [self::getTestEligibiliteEnXpIncomplet(), '/bris-de-porte/creation-de-compte'],
            'test_complet' => [self::getTestEligibiliteEnXpComplet(), '/bris-de-porte/finaliser-la-creation', true],
        ];
    }

    /**
     * ETQ visiteur invité, le lien affiche directement le formulaire de création d'espace, sans redirection. Seul le
     * courriel (l'identifiant, en lecture seule) est affiché ; les autres informations de la déclaration ne le sont
     * pas.
     */
    public function testDemarrerDepuisInvitationOk(): void
    {
        $declaration = $this->getDeclarationNonAttribueeAvecCourriel();
        $coordonneesRequerant = $declaration->getCoordonneesRequerant();

        $this->client->request('GET', "/bris-de-porte/invitation/{$declaration->getReference()}");

        $this->assertResponseIsSuccessful();
        $reactArgs = json_decode(trim($this->client->getCrawler()->filter('#react-arguments')->first()->text()), true);
        $this->assertStringContainsString($declaration->getReference(), $reactArgs['routes']['creerEspace']);
        $this->assertArrayNotHasKey('inscription', $reactArgs);
        $this->assertSame($coordonneesRequerant->getCourriel(), $reactArgs['identifiant']);
        $this->assertArrayNotHasKey('nom', $reactArgs);
        $this->assertArrayNotHasKey('prenom', $reactArgs);
        $this->assertArrayNotHasKey('telephone', $reactArgs);
    }

    public function testDemarrerDepuisInvitationKoReferenceInconnue(): void
    {
        $this->client->request('GET', '/bris-de-porte/invitation/NONNON');

        $this->assertResponseRedirects('', 302);
    }

    /**
     * ETQ visiteur, un lien d'invitation à l'ancien format (code de 6 caractères) ne doit plus être accepté.
     */
    public function testDemarrerDepuisInvitationKoAncienFormatDeCode(): void
    {
        $this->client->request('GET', '/bris-de-porte/invitation/G286QC');

        $this->assertResponseRedirects('/bris-de-porte/', 302);
    }

    /**
     * ETQ visiteur, un code au bon format mais qui ne correspond à aucune déclaration doit être refusé.
     */
    public function testDemarrerDepuisInvitationKoCodeInconnuDeMemeFormat(): void
    {
        $this->client->request('GET', '/bris-de-porte/invitation/'.str_repeat('a', 32));

        $this->assertResponseRedirects('/bris-de-porte/', 302);
    }

    /**
     * ETQ visiteur invité, créer mon espace crée le compte avec les informations de la déclaration (jamais celles du
     * formulaire, puisqu'il n'y en a pas), et le mot de passe envoyé encodé est bien décodé puis haché.
     */
    public function testCreerEspaceJsonOk(): void
    {
        $declaration = $this->getDeclarationNonAttribueeAvecCourriel();
        $courriel = $declaration->getCoordonneesRequerant()->getCourriel();
        $token = $this->getTokenCreerEspace($declaration);

        $this->client->request(
            'POST',
            "/bris-de-porte/invitation/{$declaration->getReference()}/creer-espace",
            [],
            [],
            ['HTTP_X-Csrf-Token' => $token, 'CONTENT_TYPE' => 'application/json'],
            json_encode([
                'motDePasse' => base64_encode('P4$sword'),
                'confirmation' => base64_encode('P4$sword'),
                'cguOk' => true,
            ])
        );

        $this->assertResponseStatusCodeSame(201);
        $this->em->clear();
        $usager = $this->em->getRepository(Usager::class)->findOneBy(['email' => $courriel]);
        $this->assertNotNull($usager);
        $this->assertTrue(self::getContainer()->get(UserPasswordHasherInterface::class)->isPasswordValid($usager, 'P4$sword'));
    }

    /**
     * ETQ visiteur invité, une confirmation de mot de passe différente doit être refusée.
     */
    public function testCreerEspaceJsonKoConfirmationDifferente(): void
    {
        $declaration = $this->getDeclarationNonAttribueeAvecCourriel();
        $token = $this->getTokenCreerEspace($declaration);

        $this->client->request(
            'POST',
            "/bris-de-porte/invitation/{$declaration->getReference()}/creer-espace",
            [],
            [],
            ['HTTP_X-Csrf-Token' => $token, 'CONTENT_TYPE' => 'application/json'],
            json_encode([
                'motDePasse' => base64_encode('P4$sword'),
                'confirmation' => base64_encode('Autre1234$'),
                'cguOk' => true,
            ])
        );

        $this->assertResponseStatusCodeSame(422);
    }

    /**
     * ETQ visiteur, une référence qui ne correspond à aucune déclaration doit être refusée.
     */
    public function testCreerEspaceJsonKoReferenceInconnue(): void
    {
        $declaration = $this->getDeclarationNonAttribueeAvecCourriel();
        $token = $this->getTokenCreerEspace($declaration);

        $this->client->request(
            'POST',
            '/bris-de-porte/invitation/'.str_repeat('a', 32).'/creer-espace',
            [],
            [],
            ['HTTP_X-Csrf-Token' => $token, 'CONTENT_TYPE' => 'application/json'],
            json_encode([
                'motDePasse' => base64_encode('P4$sword'),
                'confirmation' => base64_encode('P4$sword'),
                'cguOk' => true,
            ])
        );

        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * ETQ visiteur sans invitation, le courriel saisi est obligatoire : sans lui, la création de compte est refusée.
     */
    public function testCreerCompteSansInvitationCourrielObligatoire(): void
    {
        $testEligibilite = self::getTestEligibiliteEnXpIncomplet()($this->em);
        $this->initializePreinscription($testEligibilite);
        $token = $this->getTokenCreationDeCompte();

        $this->client->request('POST', '/bris-de-porte/creer-compte', $this->donneesInscription(''), [], [
            'HTTP_X-Csrf-Token' => $token,
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    protected function getDeclarationNonAttribueeAvecCourriel(): DeclarationFDOBrisPorte
    {
        // Une déclaration de fixture avec des coordonnées requérant (donc un courriel), pas encore attribuée à un dossier
        $declaration = $this->em->getRepository(DeclarationFDOBrisPorte::class)
            ->createQueryBuilder('d')
            ->andWhere('d.coordonneesRequerant IS NOT NULL')
            ->andWhere('d.brisPorte IS NULL')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        $this->assertNotNull($declaration?->getCoordonneesRequerant()?->getCourriel(), 'La déclaration de fixture doit avoir un courriel requérant');

        return $declaration;
    }

    protected function getTokenCreationDeCompte(): string
    {
        $this->client->request('GET', '/bris-de-porte/creation-de-compte');
        $reactArgs = json_decode(trim($this->client->getCrawler()->filter('#react-arguments')->first()->text()), true);

        return $reactArgs['token'];
    }

    protected function getTokenCreerEspace(DeclarationFDOBrisPorte $declaration): string
    {
        $this->client->request('GET', "/bris-de-porte/invitation/{$declaration->getReference()}");
        $reactArgs = json_decode(trim($this->client->getCrawler()->filter('#react-arguments')->first()->text()), true);

        return $reactArgs['token'];
    }

    /**
     * @return array<string, mixed>
     */
    protected function donneesInscription(string $courriel): array
    {
        return [
            'cguOk' => true,
            'civilite' => 'M',
            'prenom' => 'Rick',
            'nomNaissance' => 'Hérent',
            'nom' => 'Hérent',
            'courriel' => $courriel,
            'telephone' => '06123456789',
            'motDePasse' => 'P4ssword',
            'confirmation' => 'P4ssword',
        ];
    }

    /**
     * ETQ visiteur, après avoir rempli le formulaire de test d'éligibilité, si j'ai choisi un département en
     * expérimentation, je dois être invité à créer mon compte.
     */
    #[DataProvider('donneesCreationDeCompte')]
    public function testCreationDeCompte(?callable $getTestEligibilite = null, ?string $redirection = null): void
    {
        if ($getTestEligibilite) {
            /** @var TestEligibiliteBrisPorte $testEligibilite */
            $testEligibilite = $getTestEligibilite($this->em);
            $this->initializePreinscription($testEligibilite);
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

    public static function donneesCreationDeCompte()
    {
        return [
            'sans_test' => [null, '/bris-de-porte/tester-mon-eligibilite'],
            'test_incomplet' => [self::getTestEligibiliteEnXpIncomplet()],
            'test_complet' => [self::getTestEligibiliteEnXpComplet(), '/bris-de-porte/finaliser-la-creation'],
            // TODO rajouter une erreur opérationnelle
        ];
    }

    /**
     * ETQ visiteur, après avoir rempli le formulaire de test d'éligibilité et créé mon compte, je dois être avisé que,
     * pour continuer, je dois valider mon adresse en cliquant sur le lien figurant dans le courriel que je viens de
     * recevoir.
     */
    #[DataProvider('donneesFinaliserLaCreation')]
    public function testFinaliserLaCreation(?callable $getTestEligibilite = null, ?string $redirection = null): void
    {
        if ($getTestEligibilite) {
            /** @var TestEligibiliteBrisPorte $testEligibilite */
            $testEligibilite = $getTestEligibilite($this->em);
            $this->initializePreinscription($testEligibilite);
        }

        $this->client->request('GET', '/bris-de-porte/finaliser-la-creation');

        if ($redirection) {
            $this->assertTrue($this->client->getResponse()->isRedirect($redirection), "Je dois être redirigé vers la page '{$redirection}'");
        } else {
            $this->assertTrue($this->client->getResponse()->isSuccessful(), "Je dois pouvoir consulter la page de finalisation d'inscription");
        }
    }

    public static function donneesFinaliserLaCreation()
    {
        return [
            'sans_test' => [null, '/bris-de-porte/tester-mon-eligibilite'],
            'test_incomplet' => [self::getTestEligibiliteEnXpIncomplet(), '/bris-de-porte/creation-de-compte'],
            'test_complet' => [self::getTestEligibiliteEnXpComplet()],
        ];
    }

    protected function initializePreinscription(?TestEligibiliteBrisPorte $testEligibilite = null, ?DeclarationFDOBrisPorte $declarationErreurOperationnelle = null): void
    {
        $this->initializeSession([BrisPorteController::CLEF_SESSION_PREINSCRIPTION => [
            'testEligibilite' => $testEligibilite?->id,
            'declarationErreurOperationnelle' => $declarationErreurOperationnelle?->getId(),
            'requerant' => $testEligibilite->usager?->getId(),
        ]]);
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

    protected static function getTestEligibiliteEnXpComplet(): callable
    {
        return function (EntityManagerInterface $em) {
            $test = TestEligibiliteBrisPorte::fromArray([
                // 'description' => 'Test complet',
                'estVise' => true,
                'usager' => $em->getRepository(Usager::class)->findOneBy(['email' => 'raquel.randt@courriel.fr']),
                'dateSoumission' => new \DateTimeImmutable()->modify('-2 minutes')]);

            $em->persist($test);
            $em->flush();

            return $test;
        };
    }

    protected static function getTestEligibiliteEnXpIncomplet(): callable
    {
        return function (EntityManagerInterface $em) {
            $test = TestEligibiliteBrisPorte::fromArray([
                // 'description' => 'Test incomplet',
                'estVise' => true,
                'dateSoumission' => new \DateTimeImmutable()->modify('-2 minutes')]);

            $em->persist($test);
            $em->flush();

            return $test;
        };
    }
}
