<?php

namespace MonIndemnisationJustice\Tests\Service\Banque;

use MonIndemnisationJustice\Service\Banque\TelechargeurLogosEtablissementsBancaires;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(TelechargeurLogosEtablissementsBancaires::class)]
class TelechargeurLogosEtablissementsBancairesTest extends TestCase
{
    private const LOGO = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1 1"><rect width="1" height="1"/></svg>';

    protected string $dossier;

    protected function setUp(): void
    {
        $this->dossier = sys_get_temp_dir().'/logos_'.bin2hex(random_bytes(4));
    }

    protected function tearDown(): void
    {
        new Filesystem()->remove($this->dossier);
    }

    private function telechargeur(MockResponse ...$reponses): TelechargeurLogosEtablissementsBancaires
    {
        return new TelechargeurLogosEtablissementsBancaires(new MockHttpClient($reponses), $this->dossier);
    }

    public function testEnregistreLeLogoSousLeHashDeSonContenu(): void
    {
        $telechargeur = $this->telechargeur(
            new MockResponse(self::LOGO, ['response_headers' => ['content-type' => 'image/svg+xml']]),
            new MockResponse(self::LOGO, ['response_headers' => ['content-type' => 'image/svg+xml']]),
        );

        $nom = $telechargeur->telecharger('https://exemple.fr/a.svg');

        $this->assertMatchesRegularExpression('/^[0-9a-f]{16}\.svg$/', $nom);
        $this->assertSame(self::LOGO, file_get_contents("$this->dossier/$nom"));
        // Même contenu depuis une autre URL : même fichier, pas de doublon
        $this->assertSame($nom, $telechargeur->telecharger('https://exemple.fr/b.svg'));
        $this->assertCount(1, glob("$this->dossier/*.svg"));
        $this->assertSame([], $telechargeur->listerOrphelins([$nom]));
    }

    public function testRejetteUnDocumentQuiNEstPasUnSvg(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->telechargeur(new MockResponse('<html></html>', ['response_headers' => ['content-type' => 'text/html']]))
            ->telecharger('https://exemple.fr/page.html');
    }

    public function testRejetteUnContenuQuiNEstPasUnSvgMalgreSonType(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->telechargeur(new MockResponse('<html></html>', ['response_headers' => ['content-type' => 'image/svg+xml']]))
            ->telecharger('https://exemple.fr/faux.svg');
    }

    public function testRejetteUneErreurHttp(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->telechargeur(new MockResponse('', ['http_code' => 404]))->telecharger('https://exemple.fr/absent.svg');
    }
}
