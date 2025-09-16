<?php

namespace Api\Agent\Fip3\Endpoint\Document;

use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\BrisPorte;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class ImprimerDocumentEndpointTest extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;
    protected FilesystemOperator $storage;

    public function setUp(): void
    {
        $this->client = self::createClient(['debug' => true]);
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->storage = self::getContainer()->get('default.storage');
    }

    /**
     * ETQ agent disposant de droit d'impression sur un dossier, je dois pouvoir appeler l'_endpoint_, mettre à jour le
     * `corps` du `Document` et ainsi obtenir un fichier PDF contenant le corps de texte fourni.
     */
    public function testImpressionOk(): void
    {
        /** @var Agent $agent */
        $agent = $this->em->getRepository(Agent::class)->findOneBy(['email' => 'redacteur@justice.gouv.fr']);

        $dossier = $this->em->getRepository(BrisPorte::class)->findOneBy([
            'reference' => 'BRI/20250103/001',
        ]);
        $document = $dossier->getOrCreatePropositionIndemnisation();
        $this->em->persist($document);
        $this->em->flush();

        $this->client->loginUser($agent, 'agent');
        $this->client->request('PUT', "/api/agent/fip3/document/{$document->getId()}/imprimer", [
            'corps' => 'Lorem ipsum',
        ]);

        /** @var \stdClass $output */
        $output = json_decode($this->client->getResponse()->getContent());

        $this->assertTrue($this->client->getResponse()->isOk());
        $this->assertTrue($this->storage->has($output->filename));
        $this->assertGreaterThan(0, $output->size);
        $this->assertEquals('application/pdf', $output->mime);
        $this->assertFalse($output->estAjoutRequerant);
    }
}
