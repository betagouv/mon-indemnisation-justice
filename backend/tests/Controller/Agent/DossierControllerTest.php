<?php

namespace MonIndemnisationJustice\Tests\Controller\Agent;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\Dossier;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class DossierControllerTest extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testDossiersJson(): void
    {
        $agent = $this->em->getRepository(Agent::class)->findOneBy(['email' => 'redacteur@justice.gouv.fr']);

        $this->client->loginUser($agent, 'agent');

        $this->client->request('GET', '/agent/redacteur/dossiers.json', ['e' => 'a-finaliser']);
        $this->assertTrue($this->client->getResponse()->isOk());
        ['page' => $page, 'taille' => $taille, 'total' => $total, 'resultats' => $dossiers] = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals(1, $page);
        $this->assertEquals(2, $total);
        $this->assertCount(2, $dossiers);

        foreach ($dossiers as $donneesDossier) {
            $dossier = $this->em->getRepository(Dossier::class)->find($donneesDossier['id']);

            $this->assertArraysSimilar([
                'id' => $dossier->getId(),
                'reference' => $dossier->getReference(),
                'etat' => [
                    'id' => $dossier->getEtatDossier()->getId(),
                    'etat' => $dossier->getEtatDossier()->getEtat()->value,
                    'dateEntree' => $dossier->getEtatDossier()->getDate()->getTimestamp() * 1000,
                    'redacteur' => null,
                    'requerant' => true,
                    'contexte' => null,
                ],
                'dateDepot' => $dossier->getDateDeclaration() ? $dossier->getDateDeclaration()->getTimestamp() * 1000 : null,
                'redacteur' => $dossier->getRedacteur()?->getId(),
                'requerant' => $dossier->getUsager()->getNomCourant(capital: true),
                'adresse' => $dossier->getBrisPorte()->getAdresse()->getLibelle(),
                'estEligible' => true,
                'typeAttestation' => null,
                'issuDeclarationFDO' => false,
            ], $donneesDossier);
        }
    }

    public function testConsulterDossier(): void
    {
        $agent = $this->em->getRepository(Agent::class)->findOneBy(['email' => 'redacteur@justice.gouv.fr']);
        $dossier = $this->em->getRepository(Dossier::class)->findOneBy(['reference' => 'BRI/20250103/001']);

        $this->client->loginUser($agent, 'agent');

        // Visite de la page de détail du dossier pour le rédacteur (route "agent_redacteur_consulter_dossier", DossierController::consulterDossier)
        $this->client->request('GET', "/agent/redacteur/dossier/{$dossier->getId()}");

        $reactArguments = json_decode($this->client->getCrawler()->filter('#react-arguments')->first()->text(), true);

        $this->assertArraysSimilar($reactArguments['dossier'], [
            'id' => $dossier->getId(),
            'reference' => $dossier->getReference(),
            'etat' => [
                'id' => $dossier->getEtatDossier()->getId(),
                'etat' => $dossier->getEtatDossier()->getEtat()->value,
                'dateEntree' => $dossier->getEtatDossier()->getDate()->getTimestamp() * 1000,
                'redacteur' => $agent->getId(),
                'contexte' => null,
            ],
            'dateDepot' => $dossier->getDateDeclaration()->getTimestamp() * 1000,
            'redacteur' => $agent->getId(),
            'notes' => null,
            'testEligibilite' => [
                'estVise' => $dossier->getBrisPorte()->getTestEligibilite()->estVise,
                'estHebergeant' => $dossier->getBrisPorte()->getTestEligibilite()->estHebergeant,
                'rapportAuLogement' => $dossier->getBrisPorte()->getTestEligibilite()->rapportAuLogement->value,
                'aContacteAssurance' => $dossier->getBrisPorte()->getTestEligibilite()->aContacteAssurance,
                'aContacteBailleur' => $dossier->getBrisPorte()->getTestEligibilite()->aContacteBailleur,
            ],
            'dateOperation' => null,
            'estPorteBlindee' => false,
            'documents' => [],
            'montantIndemnisation' => null,
            'adresse' => [
                'ligne1' => $dossier->getBrisPorte()->getAdresse()->getLigne1(),
                'ligne2' => $dossier->getBrisPorte()->getAdresse()->getLigne2(),
                'codePostal' => $dossier->getBrisPorte()->getAdresse()->getCodePostal(),
                'localite' => $dossier->getBrisPorte()->getAdresse()->getLocalite(),
            ],
            'requerant' => [
                'civilite' => $dossier->getRequerantPersonne()->getCivilite()->value,
                'nom' => $dossier->getRequerantPersonne()->getNom(),
                'prenoms' => [
                    $dossier->getRequerantPersonnePhysique()->getPersonne()->getPrenom(),
                    $dossier->getRequerantPersonnePhysique()->getPrenom2(),
                    $dossier->getRequerantPersonnePhysique()->getPrenom3(),
                ],
                'nomNaissance' => null,
                'courriel' => $dossier->getUsager()->getEmail(),
                'telephone' => $dossier->getRequerantPersonnePhysique()?->getPersonne()->getTelephone(),
                'dateNaissance' => $dossier->getRequerantPersonnePhysique()?->getDateNaissance()->getTimestamp() * 1000,
                'communeNaissance' => $dossier->getRequerantPersonnePhysique()?->getCommuneNaissance(),
                'paysNaissance' => $dossier->getRequerantPersonnePhysique()?->getPaysNaissance()?->getNom(),
                'raisonSociale' => $dossier->getRequerantPersonneMorale()?->getRaisonSociale(),
                'siren' => $dossier->getRequerantPersonneMorale()?->getSirenSiret(),
            ],
            'typeAttestation' => null,
        ]);
    }

    public function assertArraysSimilar(array $expected, array $actual, string $message = 'The arrays are not similar'): void
    {
        $diff = array_diff_multidimensional($actual, $expected);
        if (count($diff) > 0) {
            dump($diff);
        }
        $this->assertEmpty($diff, $message);
    }
}
