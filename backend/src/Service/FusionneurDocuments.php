<?php

namespace MonIndemnisationJustice\Service;

use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnableToReadFile;
use MonIndemnisationJustice\Entity\Document;
use Ramsey\Uuid\Uuid;
use setasign\Fpdi\Fpdi;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

/**
 * Fusionne, dans l'ordre, les documents PDF et images d'une liste de {@see Document} en un unique fichier PDF
 * (chaque image est placée sur sa propre page, chaque page d'un PDF source est reprise telle quelle).
 */
class FusionneurDocuments
{
    private const MIME_PDF = 'application/pdf';
    private const MIMES_IMAGE_SUPPORTEES = ['image/jpeg', 'image/png', 'image/webp'];

    private const MARGE_IMAGE_MM = 10;

    private readonly Filesystem $filesystem;

    public function __construct(
        #[Target('default.storage')]
        private readonly FilesystemOperator $storage,
    ) {
        $this->filesystem = new Filesystem();
    }

    /**
     * @param Document[] $documents
     *
     * @return string Le contenu binaire du PDF fusionné
     *
     * @throws \InvalidArgumentException si la liste est vide
     * @throws FusionDocumentException   si un document empêche la fusion (type MIME non supporté, illisible, etc.)
     */
    public function fusionner(array $documents): string
    {
        if ([] === $documents) {
            throw new \InvalidArgumentException('Aucun document à fusionner');
        }

        foreach ($documents as $document) {
            $this->verifierTypeMimeSupporte($document);
        }

        $repertoireTemporaire = Path::normalize(sys_get_temp_dir().'/'.Uuid::uuid4()->toString());
        $this->filesystem->mkdir($repertoireTemporaire);

        try {
            $pdf = new Fpdi();

            foreach ($documents as $document) {
                try {
                    $cheminFichier = $this->telechargerDocument($document, $repertoireTemporaire);

                    if (self::MIME_PDF === $document->getMime()) {
                        $this->ajouterPagesPdf($pdf, $cheminFichier);
                    } else {
                        $this->ajouterPageImage($pdf, $cheminFichier, $document->getMime());
                    }
                } catch (\Throwable $e) {
                    throw new FusionDocumentException($document, sprintf('La fusion du document #%s a échoué : %s', $document->getId(), $e->getMessage()), $e);
                }
            }

            return $pdf->Output('S');
        } finally {
            $this->filesystem->remove($repertoireTemporaire);
        }
    }

    private function verifierTypeMimeSupporte(Document $document): void
    {
        $mime = $document->getMime();

        if (self::MIME_PDF === $mime || in_array($mime, self::MIMES_IMAGE_SUPPORTEES, true)) {
            return;
        }

        throw new FusionDocumentException($document, sprintf("Le document #%s a un type MIME non supporté pour la fusion ('%s'), seuls un PDF ou une image (%s) sont acceptés", $document->getId(), $mime ?? 'inconnu', implode(', ', self::MIMES_IMAGE_SUPPORTEES)));
    }

    private function telechargerDocument(Document $document, string $repertoireTemporaire): string
    {
        $cheminFichier = Path::normalize($repertoireTemporaire.'/'.Uuid::uuid4()->toString());
        $this->filesystem->dumpFile($cheminFichier, $this->storage->read($document->getFilename()));

        return $cheminFichier;
    }

    private function ajouterPagesPdf(Fpdi $pdf, string $cheminFichier): void
    {
        $nombrePages = $pdf->setSourceFile($cheminFichier);

        for ($numeroPage = 1; $numeroPage <= $nombrePages; ++$numeroPage) {
            $idModele = $pdf->importPage($numeroPage);
            $taille = $pdf->getTemplateSize($idModele);

            $pdf->AddPage($taille['orientation'], [$taille['width'], $taille['height']]);
            $pdf->useTemplate($idModele);
        }
    }

    private function ajouterPageImage(Fpdi $pdf, string $cheminFichier, string $mime): void
    {
        // FPDF ne sait pas lire le WebP nativement : on le convertit d'abord en PNG.
        if ('image/webp' === $mime) {
            $cheminFichier = $this->convertirWebpEnPng($cheminFichier);
            $mime = 'image/png';
        }

        [$largeurPx, $hauteurPx] = getimagesize($cheminFichier);

        $pdf->AddPage($largeurPx >= $hauteurPx ? 'L' : 'P', 'A4');

        $largeurDisponible = $pdf->GetPageWidth() - 2 * self::MARGE_IMAGE_MM;
        $hauteurDisponible = $pdf->GetPageHeight() - 2 * self::MARGE_IMAGE_MM;

        $ratio = min($largeurDisponible / $largeurPx, $hauteurDisponible / $hauteurPx);
        $largeur = $largeurPx * $ratio;
        $hauteur = $hauteurPx * $ratio;

        $pdf->Image(
            $cheminFichier,
            ($pdf->GetPageWidth() - $largeur) / 2,
            ($pdf->GetPageHeight() - $hauteur) / 2,
            $largeur,
            $hauteur,
            'image/jpeg' === $mime ? 'JPG' : 'PNG'
        );
    }

    private function convertirWebpEnPng(string $cheminFichierWebp): string
    {
        $image = imagecreatefromwebp($cheminFichierWebp);

        if (false === $image) {
            throw new \RuntimeException("Impossible de lire l'image WebP '$cheminFichierWebp'");
        }

        $cheminFichierPng = "$cheminFichierWebp.png";
        imagepng($image, $cheminFichierPng);

        return $cheminFichierPng;
    }
}
