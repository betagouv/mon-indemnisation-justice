<?php

namespace MonIndemnisationJustice\Service;

use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToWriteFile;
use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\MotifRejetBrisPorte;
use Psr\Log\LoggerInterface;
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
        protected readonly FusionneurDocuments $fusionneurDocuments,
        protected readonly LoggerInterface $logger,
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
        $document->getDossier()->retirerPieceJointe($document);

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

    public function genererCorps(Dossier $dossier, DocumentType $type, ?float $montantIndemnisation = null, ?MotifRejetBrisPorte $motifRejet = null): string
    {
        return $this->twig->render(
            $type->getGabaritCorps(),
            array_merge(
                [
                    'dossier' => $dossier,
                    'corps' => true,
                ],
                $montantIndemnisation ? ['montantIndemnisation' => $montantIndemnisation, 'indemnisation' => true] : [],
                $motifRejet ? ['motifRejet' => $motifRejet->value, 'indemnisation' => false] : []
            )
        );
    }

    public function generer(Dossier $dossier, DocumentType $type, ?float $montantIndemnisation = null, ?MotifRejetBrisPorte $motifRejet = null): Document
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
                $motifRejet ? ['motifRejet' => $motifRejet->value, 'indemnisation' => false] : []
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

    public function genererListeDocumentsATransmettre(Dossier $dossier): \ZipArchive
    {
        $zip = new \ZipArchive();
        $zipName = tempnam(sys_get_temp_dir(), "zip_dossier_{$dossier->getId()}");

        if (true !== $zip->open($zipName, \ZipArchive::CREATE)) {
            throw new \RuntimeException('Cannot open '.$zipName);
        }

        // Ajouter la déclaration d'acceptation et l'arrêté de paiement
        /* @var DocumentType $typeDocument */
        foreach ([DocumentType::TYPE_COURRIER_REQUERANT, DocumentType::TYPE_ARRETE_PAIEMENT] as $typeDocument) {
            /* @var Document $document */
            if (null !== ($document = $dossier->getDocumentParType($typeDocument))) {
                try {
                    $zip->addFromString(str_replace('/', '_', $typeDocument->nommerFichier($dossier)), $this->getContenuTexte($document));
                } catch (FilesystemException|UnableToReadFile $e) {
                    $this->logger->warning('Fichier de pièce jointe introuvable', ['id' => $document->getId(), 'erreur' => $e->getMessage()]);
                }
            }

        }
        // Ajouter la pièce d'identité ...
        if (count($dossier->getDocumentsParType(DocumentType::TYPE_CARTE_IDENTITE)) > 0) {
            $zip->addFromString("Pièce d'identité.pdf", $this->fusionneurDocuments->fusionner($dossier->getDocumentsParType(DocumentType::TYPE_CARTE_IDENTITE)));
        }
        // ...  le RIB ...
        if (count($dossier->getDocumentsParType(DocumentType::TYPE_RIB)) > 0) {
            $zip->addFromString("Relevé d'identité bancaire.pdf", $this->fusionneurDocuments->fusionner($dossier->getDocumentsParType(DocumentType::TYPE_RIB)));
        }
        // ... et le K-Bis
        if (count($dossier->getDocumentsParType(DocumentType::TYPE_EXTRAIT_KBIS)) > 0) {
            $zip->addFromString('Extrait K-bis.pdf', $this->fusionneurDocuments->fusionner($dossier->getDocumentsParType(DocumentType::TYPE_EXTRAIT_KBIS)));
        }

        return $zip;
    }
}
