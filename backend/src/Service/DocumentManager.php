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
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Twig\Environment;

class DocumentManager
{
    /**
     * Types de fichiers acceptés en pièce jointe, associés à leur extension de stockage.
     */
    public const array TYPES_AUTORISES = [
        'application/pdf' => 'pdf',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

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

    /**
     * Vérifie le contenu réel du fichier téléversé et renvoie son type MIME, qui doit faire partie de TYPES_AUTORISES.
     *
     * Le type est détecté dans le contenu, et non d'après le nom ou le type annoncés par le client.
     *
     * @throws BadRequestHttpException
     */
    public function verifierFichierTeleverse(UploadedFile $fichierTeleverse): string
    {
        $mime = $fichierTeleverse->getMimeType() ?? '';

        if (!array_key_exists($mime, self::TYPES_AUTORISES)) {
            throw new BadRequestHttpException('Le format du fichier n\'est pas valide (jpg, png, webp, pdf)');
        }

        return $mime;
    }

    public function ajouterFichierTeleverse(Dossier $dossier, UploadedFile $fichierTeleverse, DocumentType $type, bool $estAjoutRequerant = true): Document
    {
        $mime = $this->verifierFichierTeleverse($fichierTeleverse);

        return $this->ajouterDocument(
            $dossier,
            $dossier->getOrCreateDocument($type)
                ->setOriginalFilename($fichierTeleverse->getClientOriginalName())
                ->setType($type)
                ->setMime($mime)
                ->setAjoutRequerant($estAjoutRequerant),
            $fichierTeleverse->getContent(),
            self::TYPES_AUTORISES[$mime]
        );
    }

    public function ajouterDocument(Dossier $dossier, Document $document, string $contenu, string $extension): Document
    {
        $document = $this->enregistrerDocument($document, $contenu, $extension);

        $this->em->persist($dossier);
        $this->em->flush();

        return $document;
    }

    public function enregistrerDocument(Document $document, string $contenu, ?string $extension = null): Document
    {
        try {
            $nom = sprintf('%s.%s', hash('sha256', $contenu), $extension ?? $this->calculerExtension($document->getOriginalFilename()));
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
        $extension = pathinfo($cheminFichier, PATHINFO_EXTENSION);

        return '' !== $extension ? $extension : 'bin';
    }

    /**
     * En-têtes de restitution d'une pièce jointe. Seuls les PDF et les images sont affichés dans le navigateur ; les
     * autres types sont forcés en téléchargement, pour qu'un contenu actif (HTML, SVG) ne soit jamais exécuté dans
     * l'application.
     *
     * @return array<string, string>
     */
    public function entetesRestitution(Document $document, bool $telechargement = false): array
    {
        $mime = $document->getMime() ?? '';
        $affichageDansNavigateur = array_key_exists($mime, self::TYPES_AUTORISES);
        $nomFichier = str_replace(['"', "\r", "\n"], '', $document->getOriginalFilename());

        return [
            'Content-Type' => $affichageDansNavigateur ? $mime : 'application/octet-stream',
            'Content-Disposition' => sprintf(
                '%sfilename="%s"',
                $telechargement || !$affichageDansNavigateur ? 'attachment;' : '',
                mb_convert_encoding($nomFichier, 'ISO-8859-1', 'UTF-8')
            ),
            'X-Content-Type-Options' => 'nosniff',
        ];
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
        $this->fusionnerEtAjouterAuZip($zip, "Pièce d'identité.pdf", $dossier->getDocumentsParType(DocumentType::TYPE_CARTE_IDENTITE));
        // ...  le RIB ...
        $this->fusionnerEtAjouterAuZip($zip, "Relevé d'identité bancaire.pdf", $dossier->getDocumentsParType(DocumentType::TYPE_RIB));
        // ... et le K-Bis
        $this->fusionnerEtAjouterAuZip($zip, 'Extrait K-bis.pdf', $dossier->getDocumentsParType(DocumentType::TYPE_EXTRAIT_KBIS));

        return $zip;
    }

    /**
     * Fusionne une liste de documents en un unique fichier PDF ajouté au zip sous le nom donné.
     *
     * Les documents n'ayant pas pu être intégrés à la fusion, notamment parce que la compression du document PDF source
     * n'est pas compatible (voie https://www.setasign.com/fpdi-pdf-parser), sont ensuite ajoutés au zip directement,
     * comme pièce jointe brute.
     *
     * @param Document[] $documents
     */
    private function fusionnerEtAjouterAuZip(\ZipArchive $zip, string $nomFichier, array $documents): void
    {
        if ([] === $documents) {
            return;
        }

        [$contenuFusionne, $documentsEnEchec] = $this->fusionneurDocuments->fusionner($documents);

        if (null !== $contenuFusionne) {
            $zip->addFromString($nomFichier, $contenuFusionne);
        }

        foreach ($documentsEnEchec as $document) {
            try {
                $zip->addFromString(str_replace('/', '_', $document->getOriginalFilename()), $this->getContenuTexte($document));
            } catch (FilesystemException|UnableToReadFile $e) {
                $this->logger->warning('Fichier de pièce jointe introuvable', ['id' => $document->getId(), 'erreur' => $e->getMessage()]);
            }
        }
    }
}
