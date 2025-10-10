<?php

namespace MonIndemnisationJustice\Service;

use League\Flysystem\FilesystemOperator;
use MonIndemnisationJustice\Entity\Document;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Twig\Environment;

/**
 * Imprime, vers un document PDF, les différents documents éditables des dossiers.
 */
class ImprimanteCourrier
{
    protected readonly Filesystem $filesystem;
    protected readonly string $projectDirectory;
    protected readonly string $binDirectory;

    public function __construct(
        protected readonly Environment $twig,
        #[Autowire(param: 'kernel.project_dir')]
        string $projectDirectory,
        #[Target('default.storage')]
        protected readonly FilesystemOperator $storage,
    ) {
        $this->filesystem = new Filesystem();
        $this->projectDirectory = $projectDirectory;
        $this->binDirectory = "{$projectDirectory}/bin";
    }

    public function imprimerDocument(Document $document): Document
    {
        // Création d'un préfixe de chemin temporaire, dédié à la génération des documents HTML et PDF
        $path = Path::normalize(sys_get_temp_dir().'/'.Uuid::uuid4()->toString());

        $this->filesystem->mkdir($path);

        try {
            $fichierHtml = "{$path}/{$document->getType()->value}_{$document->getDossier()->getId()}.html";
            $fichierPdf = "{$path}/{$document->getType()->value}_{$document->getDossier()->getId()}.pdf";
            // Générer le contenu de la page HTML statique
            $this->filesystem->dumpFile($fichierHtml, $this->twig->render($document->getType()->getGabarit(), [
                'dossier' => $document->getDossier(),
                'corps' => $document?->getCorps(),
                'contexte' => $document->getMetaDonnees() ?? [],
            ]));

            $impression = new Process([$this->binDirectory.'/print.js', $fichierHtml, $fichierPdf], $this->projectDirectory);

            $impression->run();

            // executes after the command finishes
            if (!$impression->isSuccessful()) {
                throw new ProcessFailedException($impression);
            }

            if (!$this->filesystem->exists($fichierPdf)) {
                throw new \LogicException("Le fichier '{$fichierPdf}' n'a pas été créé");
            }

            $destination = hash('sha256', file_get_contents($fichierPdf)).'.pdf';

            $this->storage->write($destination, file_get_contents($fichierPdf));

            $document->setFilename($destination)
                ->setModifie()
                ->setSize(filesize($fichierPdf))
                ->setMime('application/pdf')
                ->setOriginalFilename($document->getType()->nommerFichier($document->getDossier()))
            ;

            return $document;
        } catch (\Exception $e) {
            // Supprimer les fichiers temporaires
            $this->filesystem->remove($path);

            throw new \LogicException($e->getMessage(), previous: $e);
        }
    }
}
