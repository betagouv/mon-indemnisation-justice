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

    public function imprimerCourrier(BrisPorte $dossier): string
    {
        $path = Path::normalize(sys_get_temp_dir().'/'.Uuid::uuid4()->toString());

        $this->filesystem->mkdir($path);

        try {
            // Générer le contenu de la page HTML statique
            $this->filesystem->dumpFile("$path/courrier_$dossier->id.html",
                $this->twig->render('courrier/dossier.html.twig', [
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
            if (false) { // TODO changer pour un check sur APP_DEBUG
                $this->filesystem->remove($path);
            }

            throw new \LogicException($e->getMessage(), previous: $e);
        }
    }
}
