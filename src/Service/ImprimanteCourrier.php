<?php

namespace MonIndemnisationJustice\Service;

use League\Flysystem\FilesystemOperator;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\DocumentType;
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
     * @param BrisPorte $dossier le dossier pour lequel imprimer le courrier
     *
     * @throws \League\Flysystem\FilesystemException
     */
    public function imprimerLettreDecision(BrisPorte $dossier, ?Document $document = null): Document
    {
        return $this->imprimerDocument($dossier, $document, DocumentType::TYPE_COURRIER_MINISTERE);
    }

    /**
     * Imprime l'arrêté de paiement pour un dossier.
     *
     * @param BrisPorte $dossier le dossier pour lequel imprimer le courrier
     *
     * @throws \League\Flysystem\FilesystemException
     */
    public function imprimerArretePaiement(BrisPorte $dossier, ?Document $document = null): Document
    {
        return $this->imprimerDocument($dossier, $document, DocumentType::TYPE_ARRETE_PAIEMENT);
    }

    protected function imprimerDocument(BrisPorte $dossier, ?Document $document = null, ?DocumentType $type = null): Document
    {
        // Création d'un préfixe de chemin temporaire, dédié à la génération des documents HTML et PDF
        $path = Path::normalize(sys_get_temp_dir().'/'.Uuid::uuid4()->toString());

        $document = $document ?? (new Document())->setType($type);

        $this->filesystem->mkdir($path);

        try {
            $fichierHtml = "$path/{$document->getType()->value}_{$dossier->getId()}.html";
            $fichierPdf = "$path/{$document->getType()->value}_{$dossier->getId()}.pdf";
            // Générer le contenu de la page HTML statique
            $this->filesystem->dumpFile($fichierHtml, $this->twig->render($document->getType()->getGabarit(), [
                'dossier' => $dossier,
                'corps' => $document?->getCorps(),
            ]));

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

            $document->setFilename($destination)->setSize(filesize($fichierPdf))->setMime('application/pdf');

            return $document;
        } catch (\Exception $e) {
            // Supprimer les fichiers temporaires
            $this->filesystem->remove($path);

            throw new \LogicException($e->getMessage(), previous: $e);
        }
    }
}
