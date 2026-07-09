<?php

namespace MonIndemnisationJustice\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Entity\MotifRejetBrisPorte;
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
            'mis_en_cause' => [
                MotifRejetBrisPorte::MIS_EN_CAUSE,
                [
                    'vous étiez bien concerné(e) par leur intervention',
                ],
            ],
            'locataire_hebergeant' => [
                MotifRejetBrisPorte::LOCATAIRE_HEBERGEANT,
                [
                    'les personnes recherchées étaient bien hébergées à votre domicile',
                    'le locataire doit répondre des dommages causés au logement loué',
                ],
            ],
            'locataire' => [
                MotifRejetBrisPorte::LOCATAIRE,
                [
                    'vous étiez bien concerné(e) par leur intervention',
                    'le locataire doit répondre des dommages causés au logement loué',
                ],
            ],
        ];
    }

    #[DataProvider('donneesGenererCorpsRejetOk')]
    public function testGenererCorpRejetOk(MotifRejetBrisPorte $motifRejet, array $mentions = []): void
    {
        $dossier = $this->getDossierParEtat(EtatDossierType::DOSSIER_EN_INSTRUCTION);

        $corps = $this->documentManager->genererCorps($dossier, DocumentType::TYPE_COURRIER_MINISTERE, motifRejet: $motifRejet);

        $this->assertIsString($corps);
        $crawler = new Crawler($corps);
        foreach ($mentions as $mention) {
            $this->assertStringContainsString($mention, $corps);
        }
    }

    public static function donneesGenererCourrierRejetOk(): array
    {
        return [
            'mis_en_cause' => [
                MotifRejetBrisPorte::MIS_EN_CAUSE,
                [
                    'vous étiez bien concerné(e) par leur intervention',
                ],
            ],
        ];
    }

    #[DataProvider('donneesGenererCourrierRejetOk')]
    public function testGenererCourrierRejetOk(MotifRejetBrisPorte $motifRejet, array $mentions): void
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
