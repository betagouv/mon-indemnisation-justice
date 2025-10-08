<?php

namespace MonIndemnisationJustice\Service;

use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnableToWriteFile;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\DocumentType;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Twig\Environment;

class DocumentManager
{
    public function __construct(
        #[Target('default.storage')]
        protected readonly FilesystemOperator $storage,
        protected readonly EntityManagerInterface $em,
        protected readonly ImprimanteCourrier $imprimanteCourrier,
        protected readonly Environment $twig,
    ) {}

    public function ajouterFichierLocal(BrisPorte $dossier, string $cheminOuURL, DocumentType $type, bool $estAjoutRequerant = true): void
    {
        $contenu = file_get_contents($cheminOuURL);
        if (filter_var($cheminOuURL, FILTER_VALIDATE_URL)) {
            $cheminFichier = Path::normalize(sys_get_temp_dir().'/'.Uuid::uuid4()->toString());
            file_put_contents($cheminFichier, $contenu);
        } else {
            $cheminFichier = $cheminOuURL;
        }

        $mime = mime_content_type($cheminFichier);
        $extension = pathinfo($cheminFichier, PATHINFO_EXTENSION) ?? match ($mime) {
            'application/pdf' => 'pdf',
            'image/jpeg', 'image/png', 'image/gif', 'image/webp' => preg_replace('image/', '', $mime),
        };

        $this->ajouterDocument(
            $dossier,
            $dossier->getOrCreateDocument($type)
                ->setOriginalFilename(pathinfo($cheminFichier, PATHINFO_FILENAME))
                ->setType($type)
                ->setMime($mime)
                ->setAjoutRequerant($estAjoutRequerant),
            $contenu,
            $extension
        );
    }

    public function ajouterFichierTeleverse(BrisPorte $dossier, UploadedFile $fichierTeleverse, DocumentType $type, bool $estAjoutRequerant = true): Document
    {
        return $this->ajouterDocument(
            $dossier,
            $dossier->getOrCreateDocument($type)
                ->setOriginalFilename($fichierTeleverse->getClientOriginalName())
                ->setAjoutRequerant(true)
                ->setType($type)
                ->setMime($fichierTeleverse->getClientMimeType())
                ->setAjoutRequerant($estAjoutRequerant),
            $fichierTeleverse->getContent(),
            $fichierTeleverse->guessExtension() ?? $fichierTeleverse->getExtension()
        );
    }

    public function ajouterDocument(BrisPorte $dossier, Document $document, string $contenu, string $extension): Document
    {
        $nom = sprintf('%s.%s', hash('sha256', $contenu), $extension);

        try {
            $this->storage->write($nom, $contenu);

            if (!$this->storage->fileExists($nom)) {
                throw new FileException("L'enregistrement du fichier a échoué");
            }

            $document
                ->setFilename($nom)
                ->setSize($this->storage->fileSize($nom))
            ;

            $dossier->ajouterDocument($document);
            $this->em->persist($dossier);
            $this->em->flush();

            return $document;
        } catch (FilesystemException|UnableToWriteFile $e) {
            throw new FileException("La sauvegarde du fichier a échoué: {$e->getMessage()}");
        }
    }

    public function supprimer(Document $document)
    {
        $this->storage->delete($document->getFilename());

        $this->em->remove($document);
        $this->em->flush();
    }

    /** @return resource */
    public function getContenuRessource(Document $document)
    {
        if (!$this->storage->has($document->getFilename())) {
            throw new FileException("Le fichier associé à ce document n'existe pas.");
        }

        return $this->storage->readStream($document->getFilename());
    }

    public function genererArretePaiement(BrisPorte $dossier): void
    {
        $arretePaiement = $dossier->getOrCreateArretePaiement()->setCorps(
            $this->twig->render('courrier/_corps_arretePaiement.html.twig', [
                'dossier' => $dossier,
            ])
        )->ajouterAuDossier($dossier);

        $arretePaiement = $this->imprimanteCourrier->imprimerDocument($arretePaiement)
            ->setOriginalFilename("Arrêté de paiement - dossier {$dossier->getReference()}")
        ;

        $this->em->persist($arretePaiement);
        $this->em->flush();
    }
}
