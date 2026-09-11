<?php

namespace MonIndemnisationJustice\Tests\Service;

use League\Flysystem\FilesystemOperator;
use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Service\FusionneurDocuments;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Teste le service {@see FusionneurDocuments} qui a pour objectif de fusionner une liste de document dans un seul fichier
 * PDF.
 */
#[CoversClass(FusionneurDocuments::class)]
class FusionneurDocumentsTest extends WebTestCase
{
    protected FusionneurDocuments $fusionneur;
    protected FilesystemOperator $storage;

    public function setUp(): void
    {
        self::bootKernel();

        $this->storage = static::getContainer()->get('default.storage');
        $this->fusionneur = new FusionneurDocuments($this->storage, new NullLogger());
    }

    /**
     * Teste que l'on peut fusionner correctement quatre fichiers de format différent.
     */
    public function testFusionnerPdfEtImages(): void
    {
        $documents = [
            $this->creerDocument('documents/declaration_acceptation.pdf', DocumentType::TYPE_COURRIER_REQUERANT, 'application/pdf'),
            $this->creerDocument('pieces_jointes/photo-1.jpg', DocumentType::TYPE_PHOTO_PREJUDICE, 'image/jpeg'),
            $this->creerDocument('pieces_jointes/Facture 1.png', DocumentType::TYPE_FACTURE, 'image/png'),
            $this->creerDocument('pieces_jointes/photo-3.webp', DocumentType::TYPE_PHOTO_PREJUDICE, 'image/webp'),
        ];

        [$pdfFusionne, $documentsEnEchec] = $this->fusionneur->fusionner($documents);

        $this->assertSame([], $documentsEnEchec);
        $this->assertStringStartsWith('%PDF', $pdfFusionne);
        $this->assertSame(4, $this->compterPages($pdfFusionne));
    }

    /**
     * Teste que si on fusionne deux documents dont un incompatible, on obtient un fichier PDF et le document incompatible
     * dans la liste des documents en échec.
     */
    public function testFusionnerIgnoreUnDocumentAuTypeMimeNonSupporte(): void
    {
        $documentNonSupporte = $this->creerDocument('pieces_jointes/photo-1.jpg', DocumentType::TYPE_PHOTO_PREJUDICE, 'text/plain');
        $documents = [
            $this->creerDocument('documents/declaration_acceptation.pdf', DocumentType::TYPE_COURRIER_REQUERANT, 'application/pdf'),
            $documentNonSupporte,
        ];

        [$pdfFusionne, $documentsEnEchec] = $this->fusionneur->fusionner($documents);

        $this->assertSame([$documentNonSupporte], $documentsEnEchec);
        $this->assertStringStartsWith('%PDF', $pdfFusionne);
        $this->assertSame(1, $this->compterPages($pdfFusionne));
    }

    /**
     * Test que si on fusionne un seul document incompatible, on obtient `null` et le document dans la liste en échec.
     */
    public function testFusionnerRetourneNullSiAucunDocumentNIntegre(): void
    {
        $documents = [
            $this->creerDocument('pieces_jointes/photo-1.jpg', DocumentType::TYPE_PHOTO_PREJUDICE, 'text/plain'),
        ];

        [$pdfFusionne, $documentsEnEchec] = $this->fusionneur->fusionner($documents);

        $this->assertNull($pdfFusionne);
        $this->assertSame($documents, $documentsEnEchec);
    }

    /**
     * Test que la fusion sans aucun document jette une exception.
     */
    public function testFusionnerRejetteUneListeVide(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->fusionneur->fusionner([]);
    }

    protected function creerDocument(string $cheminRessource, DocumentType $type, string $mime): Document
    {
        $contenu = file_get_contents(__DIR__."/../ressources/$cheminRessource");
        $nom = hash('sha256', $contenu).'-'.uniqid();

        $this->storage->write($nom, $contenu);

        return (new Document())
            ->setType($type)
            ->setMime($mime)
            ->setFilename($nom);
    }

    protected function creerDocumentWebp(string $cheminRessourceJpeg): Document
    {
        $image = imagecreatefromjpeg(__DIR__."/../ressources/$cheminRessourceJpeg");

        ob_start();
        imagewebp($image);
        $contenu = ob_get_clean();

        $nom = hash('sha256', $contenu).'-'.uniqid();
        $this->storage->write($nom, $contenu);

        return (new Document())
            ->setType(DocumentType::TYPE_PHOTO_PREJUDICE)
            ->setMime('image/webp')
            ->setFilename($nom);
    }

    protected function compterPages(string $contenuPdf): int
    {
        file_put_contents($tmp = (sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid().'.pdf'), $contenuPdf);

        $process = new Process(['pdfinfo', $tmp]);
        $process->run();

        unlink($tmp);

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        preg_match('/^Pages:\s*(\d+)/m', $process->getOutput(), $matches);

        return (int) $matches[1];
    }
}
