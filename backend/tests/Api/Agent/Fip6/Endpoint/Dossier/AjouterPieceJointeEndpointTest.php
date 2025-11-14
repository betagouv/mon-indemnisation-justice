<?php

namespace MonIndemnisationJustice\Tests\Api\Agent\Fip6\Endpoint\Dossier;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier\AjouterPieceJointeEndpoint;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\Endpoint\AbstractEndpointTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Teste le point d'entrée @AjouterPieceJointeEndpoint de l'API, permettant à un rédacteur ou à un agent validateur ou
 * attributeur d'ajouter une pièce jointe à un dossier.
 *
 * @internal
 *
 * @covers \MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier\AjouterPieceJointeEndpoint
 */
class AjouterPieceJointeEndpointTest extends AbstractEndpointTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;
    protected string $dossierRessources;
    protected string $dossierTeleversement;
    protected Filesystem $filesystem;
    protected array $listeFichiersExistants;

    public function setUp(): void
    {
        $this->client = self::createClient(['debug' => true]);
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
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
     * ETQ agent, je dois pouvoir ajouter une pièce jointe en téléversant un fichier.
     */
    public function testAjouterPieceJointeOk(): void
    {
        $redacteur = $this->connexion('redacteur@justice.gouv.fr');
        $dossier = $this->getDossierParEtat(EtatDossierType::DOSSIER_A_INSTRUIRE);

        $this->client->request('POST', $this->construireApiRoute([
            'id' => $dossier->getId(),
            'type' => DocumentType::TYPE_FACTURE->value,
        ]), files: ['pieceJointe' => new UploadedFile($this->dossierRessources.'/pieces_jointes/Facture 2.pdf', 'Facture 2.pdf', 'application/pdf')]);

        $this->assertTrue($this->client->getResponse()->isOk());

        /** @var \stdClass $output */
        $output = json_decode($this->client->getResponse()->getContent(), false);
        $this->assertObjectHasProperty('type', $output);
        $this->assertEquals(DocumentType::TYPE_FACTURE->value, $output->type);

        $this->em->refresh($dossier);
        $factures = $dossier->getDocumentsParType(DocumentType::TYPE_FACTURE);
        $this->assertCount(2, $factures);
        $this->assertEquals('Facture 1.png', $factures[0]->getOriginalFilename());
        $this->assertEquals('Facture 2.pdf', $factures[1]->getOriginalFilename());
    }

    protected function getApiRoute(): string
    {
        return '/api/agent/fip6/dossier/{id}/ajouter-piece-jointe/{type}';
    }
}
