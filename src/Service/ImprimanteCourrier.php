<?php

namespace MonIndemnisationJustice\Service;

use MonIndemnisationJustice\Entity\BrisPorte;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Panther\Client;
use Twig\Environment;

/**
 * Imprime, vers un document PDF, les courriers des dossiers.
 */
class ImprimanteCourrier
{
    protected readonly Filesystem $filesystem;
    protected readonly Client $browser;

    public function __construct(protected readonly Environment $twig)
    {
        $this->filesystem = new Filesystem();
        $this->browser = Client::createFirefoxClient();
    }

    public function imprimerCourrier(BrisPorte $dossier, ?string $destination = null): string
    {
        $path = Path::normalize(sys_get_temp_dir().'/'.Uuid::uuid4()->toString());

        $this->filesystem->mkdir($path);

        try {
            // Générer le contenu de la page HTML statique
            $this->filesystem->dumpFile("$path/courrier_$dossier->id.html",
                $this->twig->render('courrier/dossier_accepte.html.twig', [
                    'dossier' => $dossier,
                ])
            );

            exec("/app/bin/print.js $path/courrier_$dossier->id.html $path/courrier_$dossier->id.pdf");

            return "$path/courrier_$dossier->id.pdf";
        } catch (\Exception $e) {
            $this->filesystem->remove($path);

            throw new \LogicException($e->getMessage(), previous: $e);
        }
    }
}
