<?php

namespace MonIndemnisationJustice\Tests\Service;

use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Service\DocumentManager;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[CoversClass(DocumentManager::class)]
class DocumentManagerSecuriteTest extends WebTestCase
{
    protected DocumentManager $documentManager;

    /** @var string[] */
    protected array $fichiersTemporaires = [];

    public function setUp(): void
    {
        self::bootKernel();

        $this->documentManager = static::getContainer()->get(DocumentManager::class);
    }

    public function tearDown(): void
    {
        foreach ($this->fichiersTemporaires as $fichier) {
            @unlink($fichier);
        }

        parent::tearDown();
    }

    /**
     * ETQ requérant, un PDF valide doit être accepté en pièce jointe.
     */
    public function testPdfValideAccepte(): void
    {
        $fichier = $this->creerFichierTeleverse('facture.pdf', "%PDF-1.4\n%%EOF", 'application/pdf');

        $this->assertSame('application/pdf', $this->documentManager->verifierFichierTeleverse($fichier));
    }

    /**
     * ETQ requérant, un fichier HTML déguisé en PDF (nom et type annoncé trompeurs) doit être refusé.
     */
    public function testHtmlDeguisePourUnPdfRefuse(): void
    {
        $fichier = $this->creerFichierTeleverse('exploit.pdf', '<html><script>alert(1)</script></html>', 'application/pdf');

        $this->expectException(BadRequestHttpException::class);

        $this->documentManager->verifierFichierTeleverse($fichier);
    }

    /**
     * ETQ agent, un fichier actif (HTML) déjà stocké ne doit jamais être affiché dans le navigateur.
     */
    public function testEntetesFichierActifForceLeTelechargement(): void
    {
        $document = (new Document())->setMime('text/html')->setOriginalFilename('exploit.html');

        $entetes = $this->documentManager->entetesRestitution($document);

        $this->assertSame('application/octet-stream', $entetes['Content-Type']);
        $this->assertStringStartsWith('attachment;', $entetes['Content-Disposition']);
        $this->assertSame('nosniff', $entetes['X-Content-Type-Options']);
    }

    /**
     * ETQ agent, un PDF est affiché dans le navigateur, sauf si le téléchargement est demandé.
     */
    public function testEntetesPdfAffichableSaufTelechargement(): void
    {
        $document = (new Document())->setMime('application/pdf')->setOriginalFilename('facture.pdf');

        $affichage = $this->documentManager->entetesRestitution($document);
        $telechargement = $this->documentManager->entetesRestitution($document, true);

        $this->assertSame('application/pdf', $affichage['Content-Type']);
        $this->assertSame('filename="facture.pdf"', $affichage['Content-Disposition']);
        $this->assertStringStartsWith('attachment;', $telechargement['Content-Disposition']);
    }

    /**
     * ETQ système, le nom de fichier restitué ne doit pas pouvoir casser l'en-tête Content-Disposition.
     */
    public function testNomDeFichierNettoye(): void
    {
        $document = (new Document())->setMime('application/pdf')->setOriginalFilename("a\"b\r\nc.pdf");

        $entetes = $this->documentManager->entetesRestitution($document);

        $this->assertSame('filename="abc.pdf"', $entetes['Content-Disposition']);
    }

    protected function creerFichierTeleverse(string $nom, string $contenu, string $mimeAnnonce): UploadedFile
    {
        $chemin = tempnam(sys_get_temp_dir(), 'pj_test_');
        file_put_contents($chemin, $contenu);
        $this->fichiersTemporaires[] = $chemin;

        return new UploadedFile($chemin, $nom, $mimeAnnonce, null, true);
    }
}
