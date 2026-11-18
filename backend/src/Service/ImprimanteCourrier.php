<?php

namespace MonIndemnisationJustice\Service;

use League\Flysystem\FilesystemOperator;
use MonIndemnisationJustice\Entity\Document;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Filesystem\Filesystem;
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
        // Générer le contenu de la page HTML statique, transmis au script d'impression sur son entrée standard
        $html = $this->twig->render($document->getType()->getGabarit(), [
            'dossier' => $document->getDossier(),
            'corps' => $document->getCorps(),
            'contexte' => $document->getMetaDonnees() ?? [],
        ]);

        $impression = new Process([$this->binDirectory.'/imprimer.js'], $this->projectDirectory, input: $html);

        $impression->run();

        if (!$impression->isSuccessful()) {
            throw new ProcessFailedException($impression);
        }

        dump($impression->getOutput());

        ['contenu' => $contenu] = json_decode($impression->getOutput(), true, 512, JSON_THROW_ON_ERROR);
        $fichierPDF = base64_decode($contenu, true);
        $destination = hash('sha256', $fichierPDF).'.pdf';

        $this->storage->write($destination, $fichierPDF);

        $document->setFilename($destination)
            ->setModifie()
            ->setSize(strlen($fichierPDF))
            ->setMime('application/pdf')
            ->setOriginalFilename($document->getType()->nommerFichier($document->getDossier()));

        return $document;
    }
}
