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
     * Imprime le courrier de décision pour un dossier.
     *
     * @param BrisPorte $dossier        le dossier pour lequel imprimer le courrier
     * @param bool      $garderFichiers garder ou non les fichiers temporaires sur le disque (utile en cas de débogage)
     *
     * @throws \League\Flysystem\FilesystemException
     */
    public function imprimerLettreDecision(BrisPorte $dossier, bool $garderFichiers = false): string
    {
        return $this->imprimerDocument('courrier/decision.html.twig', "decision_dossier_$dossier->id", [
            'dossier' => $dossier,
        ], $garderFichiers);
    }

    /**
     * Imprime l'arrêté de paiement pour un dossier.
     *
     * @param BrisPorte $dossier        le dossier pour lequel imprimer le courrier
     * @param bool      $garderFichiers garder ou non les fichiers temporaires sur le disque (utile en cas de débogage)
     *
     * @throws \League\Flysystem\FilesystemException
     */
    public function imprimerArretePaiement(BrisPorte $dossier, bool $garderFichiers = false): string
    {
        return $this->imprimerDocument('courrier/arretePaiement.html.twig', "arrete_paiement_$dossier->id", [
            'dossier' => $dossier,
        ], $garderFichiers);
    }

    protected function imprimerDocument(string $gabarit, string $document, array $contexte, bool $garderFichiers = false): string
    {
        $path = Path::normalize(sys_get_temp_dir().'/'.Uuid::uuid4()->toString());

        $this->filesystem->mkdir($path);

        try {
            $fichierHtml = "$path/$document.html";
            $fichierPdf = "$path/$document.pdf";
            // Générer le contenu de la page HTML statique
            $this->filesystem->dumpFile($fichierHtml, $this->twig->render($gabarit, $contexte));

            $impression = new Process([$this->binDirectory.'/print.js', $fichierHtml, $fichierPdf], $this->projectDirectory);

            $impression->run();

            // executes after the command finishes
            if (!$impression->isSuccessful()) {
                throw new ProcessFailedException($impression);
            }

            if (!$this->filesystem->exists($fichierPdf)) {
                throw new \LogicException("Le fichier '$fichierPdf' n'a pas été créé");
            }

            $destination = hash('sha256', file_get_contents($fichierPdf)).'.pdf';

            $this->storage->write($destination, file_get_contents($fichierPdf));

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
