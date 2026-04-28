<?php

namespace MonIndemnisationJustice\Service;

use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToWriteFile;
use MonIndemnisationJustice\Entity\Dossier;
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
    ) {
    }

    public function ajouterFichierLocal(Dossier $dossier, string $cheminOuURL, DocumentType $type, bool $estAjoutRequerant = true): void
    {
        $contenu = file_get_contents($cheminOuURL);
        if (filter_var($cheminOuURL, FILTER_VALIDATE_URL)) {
            $cheminFichier = Path::normalize(sys_get_temp_dir().'/'.Uuid::uuid4()->toString());
            file_put_contents($cheminFichier, $contenu);
        } else {
            $cheminFichier = $cheminOuURL;
        }

        $mime = $this->calculerTypeMime($cheminFichier);
        $extension = $this->calculerExtension($cheminFichier);

        $this->ajouterDocument(
            $dossier,
            $dossier->getOrCreateDocument($type)
                ->setOriginalFilename($type->nommerFichier($dossier) ?? pathinfo($cheminFichier, PATHINFO_FILENAME))
                ->setType($type)
                ->setMime($mime)
                ->setAjoutRequerant($estAjoutRequerant),
            $contenu,
            $extension
        );
    }

    public function ajouterFichierTeleverse(Dossier $dossier, UploadedFile $fichierTeleverse, DocumentType $type, bool $estAjoutRequerant = true): Document
    {
        return $this->ajouterDocument(
            $dossier,
            $dossier->getOrCreateDocument($type)
                ->setOriginalFilename($fichierTeleverse->getClientOriginalName())
                ->setType($type)
                ->setMime($fichierTeleverse->getClientMimeType())
                ->setAjoutRequerant($estAjoutRequerant),
            $fichierTeleverse->getContent(),
            $fichierTeleverse->guessExtension() ?? $fichierTeleverse->getExtension()
        );
    }

    public function ajouterDocument(Dossier $dossier, Document $document, string $contenu, string $extension): Document
    {
        $document = $this->enregistrerDocument($document, $contenu);

        $this->em->persist($dossier);
        $this->em->flush();

        return $document;
    }

    public function enregistrerDocument(Document $document, string $contenu): Document
    {
        try {
            $nom = sprintf('%s.%s', hash('sha256', $contenu), $this->calculerExtension($document->getOriginalFilename()));
            $this->storage->write($nom, $contenu);

            if (!$this->storage->fileExists($nom)) {
                throw new FileException("L'enregistrement du fichier a échoué");
            }

            $document
                ->setFilename($nom)
                ->setSize($this->storage->fileSize($nom));

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

    /**
     * @return resource
     *
     * @throws UnableToReadFile
     * @throws FilesystemException
     */
    public function getContenuRessource(Document $document)
    {
        return $this->storage->readStream($document->getFilename());
    }

    /**
     * @throws UnableToReadFile
     * @throws FilesystemException
     */
    public function getContenuTexte(Document $document): string
    {
        return $this->storage->read($document->getFilename());
    }

    public function genererCorps(Dossier $dossier, DocumentType $type, ?float $montantIndemnisation = null, ?string $motifRejet = null): string
    {
        return $this->twig->render(
            $type->getGabaritCorps(),
            array_merge(
                [
                    'dossier' => $dossier,
                    'corps' => true,
                ],
                $montantIndemnisation ? ['montantIndemnisation' => $montantIndemnisation, 'indemnisation' => true] : [],
                $motifRejet ? ['motifRejet' => $motifRejet, 'indemnisation' => false] : []
            )
        );
    }

    public function generer(Dossier $dossier, DocumentType $type, ?float $montantIndemnisation = null, ?string $motifRejet = null): Document
    {
        if (!$type->estEditableAgent()) {
            throw new \LogicException("Les documents de type '$type->value' ne sont pas éditables");

        }
        $document = $dossier
            ->getOrCreateDocument($type)
            ->setCorps(
                $this->genererCorps($dossier, $type, $montantIndemnisation, $motifRejet)
            )->setMetaDonnees(array_merge(
                $montantIndemnisation ? ['montantIndemnisation' => $montantIndemnisation, 'indemnisation' => true] : [],
                $motifRejet ? ['motifRejet' => $motifRejet, 'indemnisation' => true] : []
            ));

        $document = $this->imprimanteCourrier->imprimerDocument($document)
            ->setOriginalFilename($type->nommerFichier($dossier));

        $this->em->persist($document);
        $this->em->flush();

        return $document;
    }

    public function calculerTypeMime(string $cheminFichier): string
    {
        return mime_content_type($cheminFichier);
    }

    public function calculerExtension(string $cheminFichier): string
    {
        return pathinfo($cheminFichier, PATHINFO_EXTENSION) ?? match ($mime = $this->calculerTypeMime($cheminFichier)) {
            'application/pdf' => 'pdf',
            'image/jpeg', 'image/png', 'image/gif', 'image/webp' => preg_replace('image/', '', $mime),
            default => 'txt',
        };
    }
}
