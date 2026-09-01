<?php

namespace MonIndemnisationJustice\Tests\Event\Listener;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\DossierType;
use MonIndemnisationJustice\Entity\TestEligibiliteDysfonctionnement;
use MonIndemnisationJustice\Entity\Usager;
use MonIndemnisationJustice\Event\Listener\ConnexionUsagerListener;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Le lien "test d'éligibilité dysfonctionnement -> dossier brouillon" doit fonctionner à la connexion même sans
 * la session d'origine (cf. onglet/navigateur différent en cliquant sur le lien de vérification de courriel) :
 * on se base ici sur le rattachement fait en base par InscriptionUsagerService::creerUsager, pas la session.
 */
#[CoversClass(ConnexionUsagerListener::class)]
class ConnexionUsagerListenerTest extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
    }

    public function testConnexionCreeUnDossierDysfonctionnementSansSessionEnCours(): void
    {
        $usager = $this->em->getRepository(Usager::class)->findOneBy(['email' => 'raquel.randt@courriel.fr']);

        // Un test d'éligibilité déjà rattaché à l'usager en base (comme le fait InscriptionUsagerService), mais
        // sans aucune session en cours (nouvel onglet / navigateur, comme après un clic sur le lien de courriel).
        $test = TestEligibiliteDysfonctionnement::fromArray([
            'dateDecision' => new \DateTimeImmutable('-6 months'),
            'aUneActionContentieuse' => false,
            'typesDecision' => ['jugement_premiere_instance'],
            'piecesProcedure' => ['acte_introductif'],
            'preuvesDiligences' => true,
            'usager' => $usager,
        ]);
        $this->em->persist($test);
        $this->em->flush();

        $crawler = $this->client->request('GET', '/connexion');
        $form = $crawler->selectButton('Je me connecte à mon espace')->form([
            '_username' => 'raquel.randt@courriel.fr',
            '_password' => 'P4ssword',
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects('/requerant');

        // Le client reboote le kernel entre les requêtes : on relit l'état via un EntityManager frais plutôt que
        // de réutiliser $this->em, potentiellement détaché du conteneur qui a traité la requête de connexion.
        // On vérifie via une requête fraîche sur Dossier plutôt que via $testApresConnexion->dossier : ce dernier
        // est le côté inverse de la relation, que Doctrine ne resynchronise jamais en mémoire automatiquement
        // lorsque seul le côté propriétaire (DemandeDysfonctionnement::testEligibilite) est renseigné.
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $dossier = $em->createQueryBuilder()
            ->select('dos')
            ->from(Dossier::class, 'dos')
            ->join('dos.demandeDysfonctionnement', 'dem')
            ->where('dem.testEligibilite = :test')
            ->setParameter('test', $test->id)
            ->getQuery()
            ->getOneOrNullResult();

        $this->assertNotNull($dossier, "Le test d'éligibilité aurait dû être rattaché à un dossier créé à la connexion");
        $this->assertEquals(DossierType::DYSFONCTIONNEMENT, $dossier->getType());
        $this->assertEquals($usager->getId(), $dossier->getUsager()->getId());
    }
}
