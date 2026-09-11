<?php

namespace MonIndemnisationJustice\Tests\Service;

use League\Flysystem\FilesystemOperator;
use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Service\FusionDocumentException;
use MonIndemnisationJustice\Service\FusionneurDocuments;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class FusionneurDocumentsTest extends WebTestCase
{
    protected FusionneurDocuments $fusionneur;
    protected FilesystemOperator $storage;

    public function setUp(): void
    {
        self::bootKernel();

        $this->storage = static::getContainer()->get('default.storage');
        $this->fusionneur = new FusionneurDocuments($this->storage);
    }

    public function testFusionnerPdfEtImages(): void
    {
        $documents = [
            $this->creerDocument('documents/declaration_acceptation.pdf', 'application/pdf'),
            $this->creerDocument('pieces_jointes/photo-1.jpg', 'image/jpeg'),
            $this->creerDocument('pieces_jointes/Facture 1.png', 'image/png'),
        ];

        $pdfFusionne = $this->fusionneur->fusionner($documents);

        $this->assertStringStartsWith('%PDF', $pdfFusionne);
        $this->assertSame(3, $this->compterPages($pdfFusionne));
    }

    public function testFusionnerAvecImageWebp(): void
    {
        $documents = [
            $this->creerDocument('documents/declaration_acceptation.pdf', 'application/pdf'),
            $this->creerDocumentWebp('pieces_jointes/photo-1.jpg'),
        ];

        $pdfFusionne = $this->fusionneur->fusionner($documents);

        $this->assertStringStartsWith('%PDF', $pdfFusionne);
        $this->assertSame(2, $this->compterPages($pdfFusionne));
    }

    public function testFusionnerRejetteUnTypeMimeNonSupporte(): void
    {
        $documentNonSupporte = $this->creerDocument('pieces_jointes/photo-1.jpg', 'text/plain');
        $documents = [
            $this->creerDocument('documents/declaration_acceptation.pdf', 'application/pdf'),
            $documentNonSupporte,
        ];

        try {
            $this->fusionneur->fusionner($documents);
            $this->fail('Une '.FusionDocumentException::class.' aurait dû être levée');
        } catch (FusionDocumentException $e) {
            $this->assertSame($documentNonSupporte, $e->getDocument());
        }
    }

    public function testFusionnerRejetteUneListeVide(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->fusionneur->fusionner([]);
    }

    protected function creerDocument(string $cheminRessource, string $mime): Document
    {
        $contenu = file_get_contents(__DIR__."/../ressources/$cheminRessource");
        $nom = hash('sha256', $contenu).'-'.uniqid();

        $this->storage->write($nom, $contenu);

        return (new Document())
            ->setType(DocumentType::TYPE_PHOTO_PREJUDICE)
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
