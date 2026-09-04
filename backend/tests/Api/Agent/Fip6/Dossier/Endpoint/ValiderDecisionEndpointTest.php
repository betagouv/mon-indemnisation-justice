<?php

namespace MonIndemnisationJustice\Tests\Api\Agent\Fip6\Dossier\Endpoint;

use MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier\ValiderDecisionEndpoint;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\APIEndpointTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

/**
 * Teste le point d'entrée @ValiderDecisionEndpoint de l'API, permettant à l'agent validateur de téléverser le
 * courrier de décision signé et, le cas échéant, de faire avancer le dossier à l'étape suivante.
 */
#[CoversClass(ValiderDecisionEndpoint::class)]
class ValiderDecisionEndpointTest extends APIEndpointTestCase
{
    protected string $dossierRessources;
    protected string $dossierTeleversement;
    protected Filesystem $filesystem;
    protected array $listeFichiersExistants;

    public function setUp(): void
    {
        parent::setUp();

        $this->dossierRessources = self::getContainer()->getParameter('kernel.project_dir').'/tests/ressources';
        $this->dossierTeleversement = self::getContainer()->getParameter('kernel.project_dir').'/fichiers/test/';
        $this->filesystem = new Filesystem();
        $finder = new Finder();
        $finder->files()->in($this->dossierTeleversement);
        $this->listeFichiersExistants = [];

        if ($finder->hasResults()) {
            foreach ($finder as $fichier) {
                $this->listeFichiersExistants[] = $fichier->getRealPath();
            }
        }
    }

    protected function tearDown(): void
    {
        $finder = new Finder();
        $finder->files()->in($this->dossierTeleversement);

        if ($finder->hasResults()) {
            foreach ($finder as $fichier) {
                if (!in_array($fichier, $this->listeFichiersExistants)) {
                    $this->filesystem->remove($fichier);
                }
            }
        }

        parent::tearDown();
    }

    /**
     * ETQ agent validateur, je dois pouvoir valider la décision d'un dossier à signer, ce qui le fait avancer à
     * l'étape suivante.
     */
    public function testValiderDecisionOk(): void
    {
        $validateur = $this->connexion('validateur@justice.gouv.fr');
        $dossier = $this->getDossierParEtat(EtatDossierType::DOSSIER_OK_A_SIGNER);

        $this->client->request('POST', $this->construireApiRoute(['id' => $dossier->getId()]), [
            'estValide' => 'true',
            'montantIndemnisation' => '1234.56',
        ], $this->fichierSigne());

        $this->assertTrue($this->client->getResponse()->isOk());

        $this->em->refresh($dossier);

        $this->assertEquals(EtatDossierType::DOSSIER_OK_A_APPROUVER, $dossier->getEtatDossier()->getEtat());
        $this->assertEquals($validateur->getId(), $dossier->getEtatDossier()->getAgent()->getId());
        $this->assertEquals(1234.56, $dossier->getEtatDossier()->getContexte()['montantIndemnisation']);
        $this->assertNotNull($dossier->getCourrierDecision());
        $this->assertEquals('Facture 2.pdf', $dossier->getCourrierDecision()->getOriginalFilename());
    }

    /**
     * ETQ agent validateur, je dois pouvoir téléverser un courrier de rejet signé sans faire avancer le dossier
     * lorsque je n'invalide pas la décision.
     */
    public function testValiderDecisionNonValideOk(): void
    {
        $this->connexion('validateur@justice.gouv.fr');
        $dossier = $this->getDossierParEtat(EtatDossierType::DOSSIER_KO_A_SIGNER);

        $this->client->request('POST', $this->construireApiRoute(['id' => $dossier->getId()]), [
            'estValide' => 'false',
        ], $this->fichierSigne());

        $this->assertTrue($this->client->getResponse()->isOk());

        $this->em->refresh($dossier);

        $this->assertEquals(EtatDossierType::DOSSIER_KO_A_SIGNER, $dossier->getEtatDossier()->getEtat());
        $this->assertNotNull($dossier->getCourrierDecision());
    }

    /**
     * ETQ agent non validateur, je ne dois pas pouvoir valider la décision d'un dossier.
     */
    public function testValiderDecisionKoAgentNonValidateur(): void
    {
        $this->connexion('redacteur@justice.gouv.fr');
        $dossier = $this->getDossierParEtat(EtatDossierType::DOSSIER_OK_A_SIGNER);

        $this->client->request('POST', $this->construireApiRoute(['id' => $dossier->getId()]), [
            'estValide' => 'true',
        ], $this->fichierSigne());

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    protected function fichierSigne(): array
    {
        return [
            'fichierSigne' => new UploadedFile(
                $this->dossierRessources.'/pieces_jointes/Facture 2.pdf',
                'Facture 2.pdf',
                'application/pdf'
            ),
        ];
    }

    protected function getApiRoute(): string
    {
        return '/api/agent/fip6/dossier/{id}/valider-decision';
    }
}
