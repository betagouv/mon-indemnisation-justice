<?php

namespace MonIndemnisationJustice\Service;

use League\Flysystem\FilesystemOperator;
use MonIndemnisationJustice\Entity\BrisPorte;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Twig\Environment;

/**
 * Imprime, vers un document PDF, les courriers des dossiers.
 */
class ImprimanteCourrier
{
    protected readonly Filesystem $filesystem;
    protected readonly string $projectDirectory;
    protected readonly string $binDirectory;

    public function __construct(
        protected readonly Environment $twig,
        #[Autowire(param: 'kernel.project_dir')] string $projectDirectory,
        #[Target('default.storage')] protected readonly FilesystemOperator $storage,
    ) {
        $this->filesystem = new Filesystem();
        $this->projectDirectory = $projectDirectory;
        $this->binDirectory = "$projectDirectory/bin";
    }

    /**
     * Imprime, i.e. génère un fichier PDF, le courrier de décision d'un dossier.
     *
     * @param BrisPorte $dossier        le dossier pour lequel imprimer le courrier
     * @param bool      $garderFichiers garder ou non les fichiers temporaires sur le disque (utile en cas de débogage)
     *
     * @throws \League\Flysystem\FilesystemException
     */
    public function imprimerCourrier(BrisPorte $dossier, bool $garderFichiers = false): string
    {
        $path = Path::normalize(sys_get_temp_dir().'/'.Uuid::uuid4()->toString());

        $this->filesystem->mkdir($path);

        try {
            // Générer le contenu de la page HTML statique
            $this->filesystem->dumpFile("$path/courrier_$dossier->id.html",
                $this->twig->render('courrier/decision.html.twig', [
                    'dossier' => $dossier,
                ])
            );

            $impression = new Process([$this->binDirectory.'/print.js', "$path/courrier_$dossier->id.html", "$path/courrier_$dossier->id.pdf"], $this->projectDirectory);

            $impression->run();

            // executes after the command finishes
            if (!$impression->isSuccessful()) {
                throw new ProcessFailedException($impression);
            }

            if (!$this->filesystem->exists("$path/courrier_$dossier->id.pdf")) {
                throw new \LogicException("Le fichier '$path/courrier_$dossier->id.pdf' n'a pas été créé");
            }

            $destination = hash('sha256', file_get_contents("$path/courrier_$dossier->id.pdf")).'.pdf';

            $this->storage->write($destination, file_get_contents("$path/courrier_$dossier->id.pdf"));

            return $destination;
        } catch (\Exception $e) {
            // Sauf si explicitement demandé, supprimer les fichiers temporaires
            if (!$garderFichiers) {
                $this->filesystem->remove($path);
            }

            throw new \LogicException($e->getMessage(), previous: $e);
        }
    }
}
