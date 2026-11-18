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

    /** Le timeout, en secondes, accordé à chaque étape de l'impression (overture de la page, création et modification
     * du PDF.
     */
    protected readonly int $timeout;

    public function __construct(
        protected readonly Environment $twig,
        #[Autowire(param: 'kernel.project_dir')]
        string $projectDirectory,
        #[Target('default.storage')]
        protected readonly FilesystemOperator $storage,
        #[Autowire(param: 'kernel.environment')]
        string $environment,
    ) {
        $this->filesystem = new Filesystem();
        $this->projectDirectory = $projectDirectory;
        $this->binDirectory = "{$projectDirectory}/bin";
        $this->timeout = in_array($environment, ['dev', 'test']) ? 200 : 10;
    }

    /**
     * @param bool $extraireObjects extraire la position des objets (à la source des liens `<a href="objet://<nom>" ...>`)
     *                              sur le document PDF produit. Requis notamment pour le visuel lors de la signature électronique.
     *
     * @throws \JsonException
     * @throws \League\Flysystem\FilesystemException
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function imprimerDocument(Document $document, bool $extraireObjects = false): Document
    {
        // Générer le contenu de la page HTML statique, transmis au script d'impression sur son entrée standard
        $html = $this->twig->render($document->getType()->getGabarit(), [
            'dossier' => $document->getDossier(),
            'corps' => $document->getCorps(),
            'contexte' => $document->getMetaDonnees() ?? [],
        ]);

        // Le script applique le délai à chacune de ses étapes (lancement de Firefox, chargement des polices, impression) :
        // le délai du processus n'est qu'un garde-fou, couvrant le pire cas où chaque étape va jusqu'au bout du délai.
        $impression = new Process(
            [
                $this->binDirectory.'/imprimer.js',
                "--timeout={$this->timeout}",
                ...($extraireObjects ? ['--extraire-objects'] : []),
            ],
            $this->projectDirectory,
            input: $html,
            // L'extraction des objets
            timeout: (2 + ($extraireObjects ? 1 : 0)) * $this->timeout,
        );

        $impression->run();

        if (!$impression->isSuccessful()) {
            throw new ProcessFailedException($impression);
        }

        $sortie = json_decode($impression->getOutput(), true, 512, JSON_THROW_ON_ERROR);
        $contenu = $sortie['contenu'];

        $fichierPDF = base64_decode($contenu, true);
        $destination = hash('sha256', $fichierPDF).'.pdf';

        $this->storage->write($destination, $fichierPDF);

        $document->setFilename($destination)
            ->setModifie()
            ->setSize(strlen($fichierPDF))
            ->setMime('application/pdf')
            ->setOriginalFilename($document->getType()->nommerFichier($document->getDossier()));

        if ($extraireObjects) {
            $document->setMetaDonnees([
                'objets' => $sortie['objets'],
            ], true);
        }

        return $document;
    }
}
