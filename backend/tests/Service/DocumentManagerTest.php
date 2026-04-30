<?php

namespace MonIndemnisationJustice\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Service\DocumentManager;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DocumentManagerTest extends WebTestCase
{
    protected EntityManagerInterface $em;
    protected DocumentManager $documentManager;
    protected readonly FilesystemOperator $storage;

    public function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        $this->em = $container->get(EntityManagerInterface::class);
        $this->documentManager = $container->get(DocumentManager::class);
        $this->storage = $container->get('default.storage');
    }

    public static function donneesGenererCorpsRejetOk(): array
    {
        return [
            'est_vise' => [
                'est_vise',
                8,
                [
                    [3, 'l’opération de police judiciaire ayant conduit au bris de porte du domicile visait à l’interpellation de'],
                    [4, "l'instruction de votre demande n'a pas permis de mettre en évidence un dysfonctionnement du service public de la justice"],
                ],
            ],
            'est_hebergeant' => [
                'est_hebergeant',
                8,
                [
                    [2, "l’article 7 de la Loi n° 89-462 du 6 juillet 1989 impose au locataire d’user paisiblement des locaux conformément à leur destination contractuelle et de s'assurer contre les risques dont il doit répondre en tant que locataire"],
                    [3, "hébergé à votre domicile, sans qu'il n'y ait eu d'erreur de porte de la part des"],
                ],
            ],
            'est_bailleur' => [
                'est_bailleur',
                8,
                [
                    [2, "l’article 7 de la Loi n° 89-462 du 6 juillet 1989 impose au locataire d’user paisiblement des locaux conformément à leur destination contractuelle et de s'assurer contre les risques dont il doit répondre en tant que locataire"],
                    [3, 'il appartient à votre locataire de répondre des dommages causés engageant sa responsabilité contractuelle'],
                ],
            ],
        ];
    }

    #[DataProvider('donneesGenererCorpsRejetOk')]
    public function testGenererCorpRejetOk(string $motifRejet, int $nbParagraphes, array $mentions): void
    {
        $dossier = $this->getDossierParEtat(EtatDossierType::DOSSIER_EN_INSTRUCTION);

        $corps = $this->documentManager->genererCorps($dossier, DocumentType::TYPE_COURRIER_MINISTERE, motifRejet: $motifRejet);

        $this->assertIsString($corps);
        $crawler = new Crawler($corps);
        /** @var Crawler $paragraphs */
        $paragraphs = $crawler->filter('p');
        $this->assertEquals($nbParagraphes, $paragraphs->count());
        foreach ($mentions as $mention) {
            [$numeroParagraphe, $texte] = $mention;
            $this->assertStringContainsString($texte, $paragraphs->eq($numeroParagraphe)->text());
        }
    }

    public static function donneesGenererCourrierRejetOk(): array
    {
        return [
            'est_vise' => [
                'est_vise',
                [
                    'l’opération de police judiciaire ayant conduit au bris de porte du domicile visait à l’interpellation de',
                ],
            ],
        ];
    }

    #[DataProvider('donneesGenererCourrierRejetOk')]
    public function testGenererCourrierRejetOk(string $motifRejet, array $mentions): void
    {
        $dossier = $this->getDossierParEtat(EtatDossierType::DOSSIER_EN_INSTRUCTION);

        $courrier = $this->documentManager->generer($dossier, DocumentType::TYPE_COURRIER_MINISTERE, motifRejet: $motifRejet);

        $this->assertInstanceOf(Document::class, $courrier);
        $this->assertTrue($this->storage->fileExists($courrier->getFilename()));

        $lignes = $this->extraireTexteDocument($courrier);

        foreach ($mentions as $mention) {
            $this->assertNotNull(array_find($lignes, fn ($ligne) => str_contains($ligne, $mention)));
        }
    }

    protected function extraireTexteDocument(Document $document): array
    {
        return $this->extraireTextePDf($this->storage->read($document->getFilename()));
    }

    /**
     * @return string[]
     */
    protected function extraireTextePDf(string $contenuPdf): array
    {
        file_put_contents($tmp = (sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid().'.pdf'), $contenuPdf);

        $process = new Process(['pdftotext', $tmp, '-']);
        $process->run();

        unlink($tmp);

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return array_filter(
            // On filtre les lignes vides
            array_map(
                // On nettoie les lignes de caractères blancs en début et fin de chaine
                'trim',
                explode(
                    // On découpe les "lignes" selon le caractère `\n`
                    PHP_EOL,
                    // Hack : comme `pdftotext` sort une ligne par ligne "lue" sur le document, on reconstitue les
                    // paragraphes en remplaçant les `\n` qui ne sont pas précédés d'un `.` et des éventuels caractères blancs
                    preg_replace("/([^.]\s*)\n/", '$1 ', $process->getOutput())
                )
            ),
            fn (string $ligne) => !empty($ligne)
        );
    }

    protected function getDossierParEtat(EtatDossierType $etat, int $index = 0): ?Dossier
    {
        $dossiers = $this->em->getRepository(Dossier::class)->listerDossierParEtat($etat);

        return @$dossiers[$index] ?? null;
    }
}
