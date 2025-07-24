<?php

namespace MonIndemnisationJustice\Tests\Controller\Agent;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\BrisPorte;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

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
        $dossiers = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(2, $dossiers);

        foreach ($dossiers as $donneesDossier) {
            $dossier = $this->em->getRepository(BrisPorte::class)->find($donneesDossier['id']);

            $this->assertArraysSimilar([
                'id' => $dossier->getId(),
                'reference' => $dossier->getReference(),
                'etat' => [
                    'id' => $dossier->getEtatDossier()->getId(),
                    'etat' => $dossier->getEtatDossier()->getEtat()->value,
                    'dateEntree' => $dossier->getEtatDossier()->getDate()->getTimestamp() * 1000,
                    'redacteur' => null,
                    'requerant' => true,
                ],
                'dateDepot' => $dossier->getDateDeclaration() ? $dossier->getDateDeclaration()->getTimestamp() * 1000 : null,
                'redacteur' => $dossier->getRedacteur()?->getId(),
                'requerant' => $dossier->getRequerant()->getNomCourant(capital: true),
                'adresse' => $dossier->getAdresse()->getLibelle(),
                'estEligible' => true,
                'estLieAttestation' => false,
            ], $donneesDossier);
        }
    }

    public function testConsulterDossier(): void
    {
        $agent = $this->em->getRepository(Agent::class)->findOneBy(['email' => 'redacteur@justice.gouv.fr']);
        $dossier = $this->em->getRepository(BrisPorte::class)->findOneBy(['reference' => 'BRI/20250103/001']);

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
                'redacteur' => null,
            ],
            'dateDepot' => $dossier->getDateDeclaration()->getTimestamp() * 1000,
            'redacteur' => $agent->getId(),
            'notes' => null,
            'testEligibilite' => [
                'description' => $dossier->getTestEligibilite()->description,
                'estVise' => $dossier->getTestEligibilite()->estVise,
                'estHebergeant' => $dossier->getTestEligibilite()->estHebergeant,
                'estProprietaire' => $dossier->getTestEligibilite()->estProprietaire,
                'aContacteAssurance' => $dossier->getTestEligibilite()->aContacteAssurance,
                'aContacteBailleur' => $dossier->getTestEligibilite()->aContacteBailleur,
            ],
            'dateOperation' => null,
            'estPorteBlindee' => false,
            'documents' => [],
            'montantIndemnisation' => null,
            'adresse' => [
                'ligne1' => $dossier->getAdresse()->getLigne1(),
                'ligne2' => $dossier->getAdresse()->getLigne2(),
                'codePostal' => $dossier->getAdresse()->getCodePostal(),
                'localite' => $dossier->getAdresse()->getLocalite(),
            ],
            'requerant' => [
                'civilite' => $dossier->getRequerant()->getPersonnePhysique()->getCivilite()->value,
                'nom' => $dossier->getRequerant()->getPersonnePhysique()->getNom(),
                'prenoms' => [
                    $dossier->getRequerant()->getPersonnePhysique()->getPrenom1(),
                    $dossier->getRequerant()->getPersonnePhysique()->getPrenom2(),
                    $dossier->getRequerant()->getPersonnePhysique()->getPrenom3(),
                ],
                'nomNaissance' => null,
                'courriel' => $dossier->getRequerant()->getEmail(),
                'telephone' => $dossier->getRequerant()->getPersonnePhysique()->getTelephone(),
                'dateNaissance' => $dossier->getRequerant()->getPersonnePhysique()->getDateNaissance()->getTimestamp() * 1000,
                'communeNaissance' => $dossier->getRequerant()->getPersonnePhysique()->getCommuneNaissance(),
                'paysNaissance' => $dossier->getRequerant()->getPersonnePhysique()->getPaysNaissance()?->getNom(),
                'raisonSociale' => $dossier->getRequerant()->getPersonneMorale()?->getRaisonSociale(),
                'siren' => $dossier->getRequerant()->getPersonneMorale()?->getSirenSiret(),
            ],
            'estLieAttestation' => false,
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
